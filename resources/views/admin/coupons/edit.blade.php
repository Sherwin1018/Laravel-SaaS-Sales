@extends('layouts.admin')

@section('title', 'Edit Platform Coupon')

@section('content')
    <div class="top-header">
        <h1>Edit Platform Coupon</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
            @csrf
            @method('PUT')
            @include('admin.coupons._form', ['isEdit' => true])
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-create">Update Coupon</button>
                <a href="{{ route('admin.coupons.index') }}" class="btn-create" style="background:#64748b;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
