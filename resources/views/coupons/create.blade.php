@extends('layouts.admin')

@section('title', 'Create Coupon')

@section('content')
    <div class="top-header">
        <h1>Create Tenant Coupon</h1>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('coupons.store') }}">
            @csrf
            @include('coupons._form', ['isEdit' => false])
            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-create">Save Coupon</button>
                <a href="{{ route('coupons.index') }}" class="btn-create" style="background:#64748b;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
