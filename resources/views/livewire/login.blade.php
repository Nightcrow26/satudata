<div class="container py-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Login</h5>
            <form wire:submit.prevent="login">
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" wire:model="form.email" class="form-control" name="email" id="email" placeholder="Enter your email">
                    @error('form.email')
                        <small class="text-danger d-block md-1">{{ $message }}</small>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" wire:model="form.password" class="form-control" id="password" placeholder="Enter your email">
                    @error('form.password')
                        <small class="text-danger d-block md-1">{{ $message }}</small>
                    @enderror
                </div>
                <button class="btn btn-primary" type='submit'>
                    Login
                </button>
            </form>
        </div>
    </div>
</div>
