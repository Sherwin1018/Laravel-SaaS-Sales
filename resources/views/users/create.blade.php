@extends('layouts.admin')

@section('title', 'Add Team Member')

@section('content')
    <div class="top-header">
        <h1>Add Team Member</h1>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div style="margin-bottom: 20px;">
                <label for="name" style="display: block; margin-bottom: 8px; font-weight: bold;">Name</label>
                <input type="text" name="name" id="name" required 
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('name') }}">
                @error('name')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold;">Email</label>
                <input type="email" name="email" id="email" required 
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;"
                    value="{{ old('email') }}">
                @error('email')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="role" style="display: block; margin-bottom: 8px; font-weight: bold;">Role</label>
                <select name="role" id="role" required
                    style="width: 100%; padding: 10px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="password" style="display: block; margin-bottom: 8px; font-weight: bold;">Password</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" required 
                        style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    <i class="fas fa-eye toggle-password" id="togglePassword" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6B7280;"></i>
                </div>
                @error('password')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
                <p style="margin-top: 6px; color: #475569; font-size: 12px; font-weight: 600;">
                    12-14 characters with uppercase, lowercase, number, and special character.
                </p>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const togglePassword = document.querySelector('#togglePassword');
                        const password = document.querySelector('#password');
                        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
                        const confirmPassword = document.querySelector('#password_confirmation');

                        if (togglePassword && password) {
                            togglePassword.addEventListener('click', function () {
                                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                                password.setAttribute('type', type);
                                if (type === 'text') {
                                    this.classList.remove('fa-eye');
                                    this.classList.add('fa-eye-slash');
                                } else {
                                    this.classList.remove('fa-eye-slash');
                                    this.classList.add('fa-eye');
                                }
                            });
                        }

                        if (toggleConfirmPassword && confirmPassword) {
                            toggleConfirmPassword.addEventListener('click', function () {
                                const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                                confirmPassword.setAttribute('type', type);
                                if (type === 'text') {
                                    this.classList.remove('fa-eye');
                                    this.classList.add('fa-eye-slash');
                                } else {
                                    this.classList.remove('fa-eye-slash');
                                    this.classList.add('fa-eye');
                                }
                            });
                        }
                    });
                </script>
            </div>

            <div style="margin-bottom: 20px;">
                <label for="password_confirmation" style="display: block; margin-bottom: 8px; font-weight: bold;">Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" name="password_confirmation" id="password_confirmation" required 
                        style="width: 100%; padding: 10px; padding-right: 40px; border: 1px solid #DBEAFE; border-radius: 6px;">
                    <i class="fas fa-eye toggle-password" id="toggleConfirmPassword" 
                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6B7280;"></i>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" 
                    style="padding: 10px 20px; background-color: #2563EB; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Add Member
                </button>
                <a href="{{ route('users.index') }}" 
                    style="padding: 10px 20px; background-color: #1E40AF; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Cancel
                </a>
            </div>

        </form>
    </div>
@endsection
