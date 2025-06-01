{{-- resources/views/profile/partials/update-profile-information-form.blade.php --}}

<div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text"
           class="form-control @error('name') is-invalid @enderror"
           id="name"
           name="name"
           value="{{ old('name', $user->name) }}"
           required
           autofocus
           autocomplete="name">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email"
           class="form-control @error('email') is-invalid @enderror"
           id="email"
           name="email"
           value="{{ old('email', $user->email) }}"
           required
           autocomplete="username">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-2">
            <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                @csrf
            </form>

            <p class="text-sm text-muted">
                {{ __('Your email address is unverified.') }}
                <button form="send-verification" class="btn btn-link p-0 align-baseline">
                    {{ __('Click here to re-send the verification email.') }}
                </button>
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success mt-2" role="alert">
                    {{ __('A new verification link has been sent to your email address.') }}
                </div>
            @endif
        </div>
    @endif
</div>

<div class="d-flex justify-content-start mt-4">
    <button type="submit" class="btn btn-primary">Save</button>

    @if (session('status') === 'profile-updated')
        <p class="text-sm text-success ms-3 mt-2 mb-0"
           x-data="{ show: true }"
           x-show="show"
           x-transition
           x-init="setTimeout(() => show = false, 2000)">
           {{ __('Saved.') }}
        </p>
    @endif
</div>
