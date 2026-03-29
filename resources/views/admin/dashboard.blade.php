@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')

@section('styles')
    <style>
        .sa-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sa-table {
            min-width: 640px;
        }

        .top-header--with-tools {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .landing-video-trigger {
            width: 42px;
            height: 42px;
            border: none;
            border-radius: 10px;
            background: #240E35;
            color: #fff;
            display: inline-grid;
            place-items: center;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(36, 14, 53, 0.22);
        }

        .landing-video-trigger i {
            font-size: 16px;
        }

        .landing-video-modal {
            position: fixed;
            inset: 0;
            z-index: 1200;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.55);
        }

        .landing-video-modal.open {
            display: flex;
        }

        .landing-confirm-backdrop {
            position: fixed;
            inset: 0;
            z-index: 1300;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, 0.62);
        }

        .landing-confirm-backdrop.open {
            display: flex;
        }

        .landing-confirm-dialog {
            width: min(420px, 92vw);
            background: #1F2430;
            color: #fff;
            border-radius: 12px;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.35);
            padding: 18px;
        }

        .landing-confirm-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .landing-confirm-copy {
            margin: 10px 0 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .landing-confirm-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 18px;
        }

        .landing-confirm-actions button {
            border: 0;
            border-radius: 8px;
            padding: 8px 14px;
            cursor: pointer;
            font-weight: 700;
        }

        .landing-confirm-actions .confirm {
            background: #2563EB;
            color: #fff;
        }

        .landing-confirm-actions .cancel {
            background: #374151;
            color: #fff;
        }

        .landing-video-dialog {
            width: min(1180px, 96vw);
            max-height: calc(100vh - 40px);
            overflow: auto;
            background: #fff;
            border-radius: 14px;
            padding: 22px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
        }

        .landing-video-dialog-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .landing-video-dialog-top h3 {
            margin: 0;
        }

        .landing-video-close {
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 8px;
            background: #EEF2FF;
            color: #1F2937;
            cursor: pointer;
            display: inline-grid;
            place-items: center;
        }

        .landing-video-modal-copy {
            margin: 8px 0 0;
            color: #6B7280;
        }

        .landing-video-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 18px;
            align-items: start;
        }

        .landing-video-preview {
            border: 1px solid #E6E1EF;
            border-radius: 14px;
            background: #171936;
            min-height: 220px;
            padding: 18px;
            display: grid;
            place-items: center;
            color: #fff;
            text-align: center;
        }

        .landing-video-preview video {
            width: 100%;
            border-radius: 10px;
            max-height: 280px;
            background: #0F1025;
        }

        .landing-video-fallback button {
            width: 74px;
            height: 74px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.34);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-size: 24px;
            margin-bottom: 14px;
        }

        .landing-video-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
        }

        .landing-video-form input[type="file"],
        .landing-video-form input[type="number"] {
            width: 100%;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 14px;
            background: #fff;
            box-sizing: border-box;
        }

        .landing-video-dimensions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .landing-video-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }

        .landing-video-actions button {
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .landing-video-actions .upload {
            background: #240E35;
            color: #fff;
        }

        .landing-video-actions .delete {
            background: #B91C1C;
            color: #fff;
        }

        @media (max-width: 980px) {
            .landing-video-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="top-header top-header--with-tools">
        <h1>Welcome, Super Admin</h1>
        <button type="button" class="landing-video-trigger" id="landingVideoTrigger" aria-label="Open landing hero video settings">
            <i class="fas fa-film"></i>
        </button>
    </div>

    <div class="landing-video-modal {{ $errors->has('hero_video') || $errors->has('video_width') || $errors->has('video_height') ? 'open' : '' }}" id="landingVideoModal" aria-hidden="{{ $errors->has('hero_video') || $errors->has('video_width') || $errors->has('video_height') ? 'false' : 'true' }}">
        <div class="landing-video-dialog" role="dialog" aria-modal="true" aria-labelledby="landingVideoModalTitle">
            <div class="landing-video-dialog-top">
                <h3 id="landingVideoModalTitle">Landing Hero Video</h3>
                <button type="button" class="landing-video-close" id="landingVideoClose" aria-label="Close landing hero video settings">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <p class="landing-video-modal-copy">
            Upload an MP4 (max 25 MB) to show in the landing hero demo card. If empty, the fallback card UI is shown.
        </p>
        <div class="landing-video-grid" style="margin-top: 14px;">
            <div class="landing-video-preview">
                @if($landingHeroVideoUrl)
                    <video controls preload="metadata">
                        <source src="{{ $landingHeroVideoUrl }}" type="video/mp4">
                    </video>
                @else
                    <div class="landing-video-fallback">
                        <button type="button" aria-hidden="true">&#9658;</button>
                        <h4 style="margin: 0 0 6px;">Watch Product Demo</h4>
                        <p style="margin: 0; color: #15D6A2; font-weight: 700;">3 minutes</p>
                    </div>
                @endif
            </div>

            <div class="landing-video-form">
                <form id="landingVideoUploadForm" method="POST" action="{{ route('admin.landing-video.update') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="hero_video">MP4 Video File</label>
                    <input type="file" id="hero_video" name="hero_video" accept="video/mp4" required>

                    <div class="landing-video-dimensions">
                        <div>
                            <label for="video_width">Video Width</label>
                            <input type="number" id="video_width" name="video_width" min="320" max="3840" value="{{ old('video_width', $landingHeroVideoWidth) }}" required>
                        </div>
                        <div>
                            <label for="video_height">Video Height</label>
                            <input type="number" id="video_height" name="video_height" min="180" max="2160" value="{{ old('video_height', $landingHeroVideoHeight) }}" required>
                        </div>
                    </div>

                    @if($errors->has('hero_video') || $errors->has('video_width') || $errors->has('video_height'))
                        <p style="color: #B91C1C; margin: 0 0 8px;">
                            {{ $errors->first('hero_video') ?: ($errors->first('video_width') ?: $errors->first('video_height')) }}
                        </p>
                    @endif
                </form>

                <div class="landing-video-actions">
                    <button type="submit" form="landingVideoUploadForm" class="upload">Upload / Replace</button>
                @if($landingHeroVideoUrl)
                    <form method="POST" action="{{ route('admin.landing-video.delete') }}" id="landingVideoDeleteForm">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete">Delete Video</button>
                    </form>
                @endif
                </div>
            </div>
        </div>
        </div>
    </div>

    <div class="landing-confirm-backdrop" id="landingDeleteConfirm" aria-hidden="true">
        <div class="landing-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="landingDeleteConfirmTitle">
            <h3 class="landing-confirm-title" id="landingDeleteConfirmTitle">Delete video</h3>
            <p class="landing-confirm-copy">Delete the current landing hero video?</p>
            <div class="landing-confirm-actions">
                <button type="button" class="cancel" id="landingDeleteCancel">Cancel</button>
                <button type="button" class="confirm" id="landingDeleteProceed">OK</button>
            </div>
        </div>
    </div>

    <div class="kpi-cards">
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Total Tenants</h3>
            <p>{{ $tenantCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Active Tenants</h3>
            <p>{{ $activeTenantCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.tenants.index') }}'" style="cursor: pointer;">
            <h3>Trial Tenants</h3>
            <p>{{ $trialTenantCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.users.index') }}'" style="cursor: pointer;">
            <h3>Total Users</h3>
            <p>{{ $userCount }}</p>
        </div>
        <div class="card" onclick="window.location='{{ route('admin.leads.index') }}'" style="cursor: pointer;">
            <h3>Total Leads</h3>
            <p>{{ $leadCount }}</p>
        </div>
        <div class="card">
            <h3>MRR (Paid This Month)</h3>
            <p>₱{{ number_format($mrr, 2) }}</p>
        </div>
    </div>

    <div class="charts">
        <div class="chart">
            <h3>Platform Lead Volume Trend</h3>
            <canvas id="leadTrendChart"></canvas>
        </div>
        <div class="chart">
            <h3>Total Users by Role</h3>
            <canvas id="usersByRoleChart"></canvas>
        </div>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Payment Status Totals</h3>
        <div class="sa-table-scroll">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Transactions</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach(['paid', 'pending', 'failed'] as $status)
                    @php
                        $row = $paymentStatusTotals->get($status);
                    @endphp
                    <tr>
                        <td>{{ ucfirst($status) }}</td>
                        <td>{{ (int) ($row->count ?? 0) }}</td>
                        <td>₱{{ number_format((float) ($row->total ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="card">
        <h3>Needs Action Now</h3>
        <div class="sa-table-scroll">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Plan</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actionableTenants as $tenant)
                    <tr>
                        <td>{{ $tenant->company_name }}</td>
                        <td>{{ ucfirst($tenant->status) }}</td>
                        <td>{{ $tenant->subscription_plan }}</td>
                        <td>{{ $tenant->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No trial/inactive tenants found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div style="margin-top: 16px;">
            {{ $actionableTenants->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const landingVideoModal = document.getElementById('landingVideoModal');
        const landingVideoTrigger = document.getElementById('landingVideoTrigger');
        const landingVideoClose = document.getElementById('landingVideoClose');
        const landingVideoDeleteForm = document.getElementById('landingVideoDeleteForm');
        const landingDeleteConfirm = document.getElementById('landingDeleteConfirm');
        const landingDeleteCancel = document.getElementById('landingDeleteCancel');
        const landingDeleteProceed = document.getElementById('landingDeleteProceed');

        if (landingVideoModal && landingVideoTrigger && landingVideoClose) {
            const openLandingVideoModal = () => {
                landingVideoModal.classList.add('open');
                landingVideoModal.setAttribute('aria-hidden', 'false');
            };

            const closeLandingVideoModal = () => {
                landingVideoModal.classList.remove('open');
                landingVideoModal.setAttribute('aria-hidden', 'true');
            };

            landingVideoTrigger.addEventListener('click', openLandingVideoModal);
            landingVideoClose.addEventListener('click', closeLandingVideoModal);

            landingVideoModal.addEventListener('click', (event) => {
                if (event.target === landingVideoModal) {
                    closeLandingVideoModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && landingVideoModal.classList.contains('open')) {
                    closeLandingVideoModal();
                }
            });
        }

        if (landingVideoDeleteForm && landingDeleteConfirm && landingDeleteCancel && landingDeleteProceed) {
            const openDeleteConfirm = () => {
                landingDeleteConfirm.classList.add('open');
                landingDeleteConfirm.setAttribute('aria-hidden', 'false');
            };

            const closeDeleteConfirm = () => {
                landingDeleteConfirm.classList.remove('open');
                landingDeleteConfirm.setAttribute('aria-hidden', 'true');
            };

            landingVideoDeleteForm.addEventListener('submit', (event) => {
                event.preventDefault();
                openDeleteConfirm();
            });

            landingDeleteCancel.addEventListener('click', closeDeleteConfirm);
            landingDeleteProceed.addEventListener('click', () => {
                landingVideoDeleteForm.submit();
            });

            landingDeleteConfirm.addEventListener('click', (event) => {
                if (event.target === landingDeleteConfirm) {
                    closeDeleteConfirm();
                }
            });
        }

        const leadTrendCtx = document.getElementById('leadTrendChart').getContext('2d');
        new Chart(leadTrendCtx, {
            type: 'line',
            data: {
                labels: @json($leadTrendLabels),
                datasets: [{
                    label: 'Leads',
                    data: @json($leadTrendValues),
                    borderColor: '#240E35',
                    backgroundColor: 'rgba(36, 14, 53, 0.18)',
                    fill: true,
                    tension: 0.35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const roleCtx = document.getElementById('usersByRoleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'bar',
            data: {
                labels: @json($usersByRole->pluck('name')->values()),
                datasets: [{
                    label: 'Users',
                    data: @json($usersByRole->pluck('users_count')->values()),
                    backgroundColor: '#6B4A7A'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
@endsection
