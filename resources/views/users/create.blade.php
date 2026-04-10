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
                    style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                    value="{{ old('name') }}">
                @error('name')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="email" style="display: block; margin-bottom: 8px; font-weight: bold;">Email</label>
                <input type="email" name="email" id="email" required 
                    style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                    value="{{ old('email') }}">
                @error('email')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="phone" style="display: block; margin-bottom: 8px; font-weight: bold;">Phone (optional)</label>
                <input type="text" name="phone" id="phone"
                    style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;"
                    value="{{ old('phone') }}">
                @error('phone')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label for="role" style="display: block; margin-bottom: 8px; font-weight: bold;">Role</label>
                <select name="role" id="role" required
                    style="width: 100%; padding: 10px; border: 1px solid var(--theme-border, #E6E1EF); border-radius: 6px;">
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <span style="color: red; font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <p style="margin: 0; color: #475569; font-size: 13px; line-height: 1.5;">
                    A one-time setup link will be sent automatically. The invited user will verify email and set their own password.
                </p>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" 
                    style="padding: 10px 20px; background-color: var(--theme-primary, #240E35); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Add Member
                </button>
                <a href="{{ route('users.index') }}" 
                    style="padding: 10px 20px; background-color: var(--theme-primary-dark, #2E1244); color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    Cancel
                </a>
            </div>

        </form>
    </div>
@endsection
