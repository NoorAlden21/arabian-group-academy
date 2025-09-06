<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComplaintService
{
    /** تحديد بروفايل المُشتكي من المستخدم */
    protected function resolveComplainantProfile(User $user): StudentProfile|TeacherProfile
    {
        if ($user->hasRole('student')) {
            $sp = StudentProfile::query()->where('user_id', $user->id)->first();
            if ($sp) return $sp;
        }
        if ($user->hasRole('teacher')) {
            $tp = TeacherProfile::query()->where('user_id', $user->id)->first();
            if ($tp) return $tp;
        }
        throw ValidationException::withMessages([
            'profile' => 'لا يوجد بروفايل صالح للمستخدم لإرسال الشكوى.',
        ]);
    }

    public function createComplaint(User $authUser, array $data): Complaint
    {
        $complainant = $this->resolveComplainantProfile($authUser);

        $targetClass = Complaint::classFromLabel($data['target_type']);
        /** @var StudentProfile|TeacherProfile $target */
        $target = $targetClass::query()->findOrFail((int) $data['target_id']);

        // to not complain about himself
        if (
            get_class($complainant) === get_class($target)
            && (int) $complainant->getKey() === (int) $target->getKey()
        ) {
            throw ValidationException::withMessages([
                'target_id' => 'لا يمكنك تقديم شكوى على نفسك.',
            ]);
        }

        return DB::transaction(function () use ($complainant, $target, $data) {
            $complaint = new Complaint([
                'topic'       => $data['topic'],
                'description' => $data['description'],
                'status'      => 'pending',
            ]);

            $complaint->complainantable()->associate($complainant);
            $complaint->targetable()->associate($target);
            $complaint->save();

            return $complaint->fresh(['complainantable', 'targetable']);
        });
    }

    public function listMyComplaints(User $authUser, int $perPage = 15): LengthAwarePaginator
    {
        $complainant = $this->resolveComplainantProfile($authUser);

        return Complaint::query()
            ->whereMorphedTo('complainantable', $complainant)
            ->latest('id')
            ->paginate($perPage);
    }

    public function adminIndex(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Complaint::query()
            ->with(['complainantable', 'targetable', 'handler'])
            ->when(isset($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']))
            ->when(isset($filters['topic']), fn (Builder $q) => $q->where('topic', $filters['topic']))
            ->when(isset($filters['target_type']), function (Builder $q) use ($filters) {
                $class = Complaint::classFromLabel($filters['target_type']);
                if ($class) {
                    $q->where('targetable_type', $class);
                }
            })
            // فلاتر اختيارية بالمعرّف
            ->when(isset($filters['complainant_profile'], $filters['complainant_type']), function (Builder $q) use ($filters) {
                $class = Complaint::classFromLabel($filters['complainant_type']);
                if ($class) {
                    $q->where('complainantable_type', $class)
                        ->where('complainantable_id', (int) $filters['complainant_profile']);
                }
            })
            ->when(isset($filters['target_profile'], $filters['target_type']), function (Builder $q) use ($filters) {
                $class = Complaint::classFromLabel($filters['target_type']);
                if ($class) {
                    $q->where('targetable_type', $class)
                        ->where('targetable_id', (int) $filters['target_profile']);
                }
            })
            ->latest('id')
            ->paginate($perPage);
    }

    /** تحديث حالة الشكوى (إداري) */
    public function updateStatus(Complaint $complaint, string $status, User $admin): Complaint
    {
        if (!in_array($status, Complaint::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'حالة غير صالحة.']);
        }

        $complaint->update([
            'status'             => $status,
            'handled_by_user_id' => $admin->id,
            'handled_at'         => Carbon::now(),
        ]);

        return $complaint->fresh(['complainantable', 'targetable', 'handler']);
    }

    public function delete(Complaint $complaint): void
    {
        $complaint->delete();
    }

    public function topics(): array
    {
        return Complaint::TOPICS;
    }
}
