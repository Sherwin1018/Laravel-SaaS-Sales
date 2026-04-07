@php $modalMode = $modalMode ?? false; @endphp

@if(!$modalMode)
@extends('layouts.admin')

@section('title', 'Funnel Reviews')

@section('styles')
    <style>
        .reviews-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .reviews-filter{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
        .reviews-filter select{padding:10px 12px;border:1px solid #E6E1EF;border-radius:10px;background:#fff}
        .reviews-list{display:grid;gap:14px}
        .review-card{background:#fff;border:1px solid #E6E1EF;border-radius:16px;padding:18px;box-shadow:0 12px 24px rgba(15,23,42,.06)}
        .review-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;margin-bottom:10px}
        .review-name{font-size:18px;font-weight:800;color:#240E35}
        .review-meta{font-size:13px;color:#64748B}
        .review-stars{color:#f59e0b;font-size:15px;letter-spacing:.06em}
        .review-status{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
        .review-status.pending{background:#fff7ed;color:#c2410c}
        .review-status.approved{background:#ecfdf5;color:#047857}
        .review-status.rejected{background:#fef2f2;color:#b91c1c}
        .review-body{font-size:14px;line-height:1.6;color:#334155;white-space:pre-wrap}
        .review-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
        .review-btn{padding:9px 12px;border-radius:10px;border:1px solid #E6E1EF;background:#fff;color:#240E35;font-weight:700;cursor:pointer}
        .review-btn.approve{background:#047857;color:#fff;border-color:#047857}
        .review-btn.reject{background:#b91c1c;color:#fff;border-color:#b91c1c}
    </style>
@endsection

@section('content')
    <div class="top-header">
        <h1>Funnel Reviews</h1>
    </div>

    @include('funnels.reviews._content')
@endsection

@else
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funnel Reviews</title>
    <style>
        body{margin:0;padding:18px;background:#f8fafc;color:#0f172a;font-family:Arial,sans-serif}
        .reviews-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
        .reviews-filter{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
        .reviews-filter select{padding:10px 12px;border:1px solid #E6E1EF;border-radius:10px;background:#fff}
        .reviews-list{display:grid;gap:14px}
        .review-card,.card{background:#fff;border:1px solid #E6E1EF;border-radius:16px;padding:18px;box-shadow:0 12px 24px rgba(15,23,42,.06)}
        .review-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;margin-bottom:10px}
        .review-name{font-size:18px;font-weight:800;color:#240E35}
        .review-meta{font-size:13px;color:#64748B}
        .review-stars{color:#f59e0b;font-size:15px;letter-spacing:.06em}
        .review-status{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
        .review-status.pending{background:#fff7ed;color:#c2410c}
        .review-status.approved{background:#ecfdf5;color:#047857}
        .review-status.rejected{background:#fef2f2;color:#b91c1c}
        .review-body{font-size:14px;line-height:1.6;color:#334155;white-space:pre-wrap}
        .review-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
        .review-btn{padding:9px 12px;border-radius:10px;border:1px solid #E6E1EF;background:#fff;color:#240E35;font-weight:700;cursor:pointer}
        .review-btn.approve{background:#047857;color:#fff;border-color:#047857}
        .review-btn.reject{background:#b91c1c;color:#fff;border-color:#b91c1c}
    </style>
</head>
<body>
    @include('funnels.reviews._content')
</body>
</html>
@endif
