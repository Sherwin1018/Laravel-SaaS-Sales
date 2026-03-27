@extends('layouts.admin')

@section('title', 'Add New Plan')

@section('content')
    <div class="top-header">
        <h1>Add New Plan</h1>
    </div>

    <div class="card" style="max-width: 760px; margin: 0 auto;">
        <form action="{{ route('admin.plans.store') }}" method="POST">
            @csrf
            @include('admin.plans._form')

            <div style="display:flex;gap:10px;">
                <button type="submit" style="padding:10px 20px;background-color:var(--theme-primary, #240E35);color:white;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                    Create Plan
                </button>
                <a href="{{ route('admin.plans.index') }}" style="padding:10px 20px;background-color:var(--theme-primary-dark, #2E1244);color:white;text-decoration:none;border-radius:6px;font-weight:600;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
