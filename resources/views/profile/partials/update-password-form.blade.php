{{-- resources/views/profile/partials/update-password-form.blade.php --}}

<div class="mb-3">
    <label for="update_password_current_password" class="form-label">Current Password</label>
    <input type="password"
           name="current_password"
           id="update_password_current_password"
           autocomplete="current-password"
           class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
    @error('current_password', 'updatePassword')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="update_password_password" class="form-label">New Password</label>
    <input type="password"
           name="password"
           id="update_password_password"
           autocomplete="new-password"
           class="form-control @error('password', 'updatePassword') is-invalid @enderror">
    @error('password', 'updatePassword')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
    <input type="password"
           name="password_confirmation"
           id="update_password_password_confirmation"
           autocomplete="new-password"
           class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror">
    @error('password_confirmation', 'updatePassword')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex justify-content-start mt-4">
    <button type="submit" class="btn btn-primary">Save</button>

    @if (session('status') === 'password-updated')
        <p class="text-sm text-success ms-3 mt-2 mb-0"
           x-data="{ show: true }"
           x-show="show"
           x-transition
           x-init="setTimeout(() => show = false, 2000)">
           {{ __('Password updated.') }}
        </p>
    @endif
</div>
