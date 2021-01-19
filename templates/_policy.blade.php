namespace {{ $appNamespace }}\Policies;
@php
    $Entity = $tableName->to('studly singular');
    $entity = $tableName->to('snake singular');
    $entities = $tableName->to('snake plural');
@endphp
use App\Models\{{ $Entity }};
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class {{ $Entity }}Policy
{
    use HandlesAuthorization;
    {{-- use AdminTrait; --}}

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view own {{ $entities }}') || $user->hasPermissionTo('view {{ $entities }}');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\{{ $Entity }}  ${{ $entity }}
     * @return mixed
     */
    public function view(User $user, {{ $Entity }} ${{ $entity }})
    {
        if ($user->hasPermissionTo('view {{ $entities }}')) {
            return true;
        }

        if ($user->hasPermissionTo('view own {{ $entities }}')) {
            return $user->id === ${{ $entity }}->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('manage {{ $entities }}');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\{{ $Entity }}  ${{ $entity }}
     * @return mixed
     */
    public function update(User $user, {{ $Entity }} ${{ $entity }})
    {
        //
        return $user->hasPermissionTo('manage {{ $entities }}');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\{{ $Entity }}  ${{ $entity }}
     * @return mixed
     */
    public function delete(User $user, {{ $Entity }} ${{ $entity }})
    {
        //
        return $user->hasPermissionTo('manage {{ $entities }}');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\{{ $Entity }}  ${{ $entity }}
     * @return mixed
     */
    public function restore(User $user, {{ $Entity }} ${{ $entity }})
    {
        //
        return $user->hasPermissionTo('manage {{ $entities }}');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\{{ $Entity }}  ${{ $entity }}
     * @return mixed
     */
    public function forceDelete(User $user, {{ $Entity }} ${{ $entity }})
    {
        //
        return $user->hasPermissionTo('manage {{ $entities }}');
    }
}
