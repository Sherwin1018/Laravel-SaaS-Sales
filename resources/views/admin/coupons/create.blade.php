@extends('layouts.admin')

@section('title', 'Create Platform Coupon')

@section('content')
    <div class="top-header">
        <h1>Create Platform Coupon</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @csrf
            @include('admin.coupons._form', ['isEdit' => false])
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-create">Save Coupon</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn-create" style="background:#64748b;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
