@extends('layouts.admin')

@section('title', 'Funnel Builder')

@section('styles')
    <style>
        .funnels-table-scroll {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .funnels-table {
            min-width: 760px;
        }
        .fb-modal{position:fixed;inset:0;background:rgba(15,23,42,.56);backdrop-filter:blur(3px);display:none;align-items:center;justify-content:center;z-index:1500;padding:18px}
        .fb-modal.open{display:flex}
        .fb-modal-card{width:min(520px,92vw);background:#fff;border-radius:16px;border:1px solid #E6E1EF;box-shadow:0 24px 60px rgba(15,23,42,.2);padding:18px}
        .fb-modal-title{font-size:16px;font-weight:900;color:#240E35;margin:0 0 8px}
        .fb-modal-desc{font-size:13px;color:#475569;line-height:1.5;margin:0 0 16px}
        .fb-modal-actions{display:flex;justify-content:flex-end;gap:8px}
        .fb-btn{padding:8px 12px;border-radius:8px;border:1px solid #E6E1EF;background:#fff;color:#240E35;font-weight:700;cursor:pointer}
        .fb-btn.danger{background:#dc2626;color:#fff;border-color:#b91c1c}
    </style>
@endsection

@section('content')
    <div class="top-header">
        <h1>Funnel Builder</h1>
    </div>

    <div class="actions" style="justify-content: space-between; align-items: center;">
        <a href="{{ route('funnels.create') }}" class="btn-create"><i class="fas fa-plus"></i> New Funnel</a>
    </div>

    <div class="card">
        <h3>Funnels</h3>
        <div class="funnels-table-scroll">
        <table class="funnels-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Steps</th>
                    <th>Public URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($funnels as $funnel)
                    <tr>
                        <td>{{ $funnel->name }}</td>
                        <td>{{ ucfirst($funnel->status) }}</td>
                        <td>{{ $funnel->steps_count }}</td>
                        <td>
                            @if($funnel->status === 'published')
                                <a href="{{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => 'landing']) }}" target="_blank">
                                    {{ route('funnels.portal.step', ['funnelSlug' => $funnel->slug, 'stepSlug' => 'landing']) }}
                                </a>
                            @else
                                <span style="color: var(--theme-muted, #6B7280);">Publish to enable</span>
                            @endif
                        </td>
                        <td style="display:flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                            <a href="{{ route('funnels.edit', $funnel) }}" style="color:var(--theme-primary, #240E35); text-decoration:none; font-weight:700; font-size: 12px;">
                                <i class="fas fa-pen"></i> Builder
                            </a>
                            @if($funnel->status === 'published')
                                <button type="button" class="utm-generator-btn" data-funnel-id="{{ $funnel->id }}" data-funnel-slug="{{ $funnel->slug }}" data-funnel-name="{{ $funnel->name }}" style="background: linear-gradient(135deg, #6B4A7A, #8B5A8C); color:#fff; border:none; padding:6px 10px; border-radius:6px; cursor:pointer; font-weight:700; font-size:11px; display: flex; align-items: center; gap: 4px; transition: all 0.2s ease;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(107, 74, 122, 0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <i class="fas fa-link"></i> UTM
                                </button>
                            @endif
                            <form method="POST" action="{{ route('funnels.destroy', $funnel) }}" data-confirm-message="Delete this funnel?" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:#DC2626;cursor:pointer;font-weight:700; font-size: 12px;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center;">No funnels found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $funnels->links('pagination::bootstrap-4') }}
        </div>
    </div>
    
    <!-- UTM Generator Modal -->
    <div class="utm-generator-modal" id="utmGeneratorModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); z-index: 2000; align-items: center; justify-content: center; padding: 20px;">
        <div class="utm-generator-card" style="width: min(600px, 95vw); max-height: 90vh; overflow-y: auto; background: #fff; border-radius: 16px; border: 1px solid #E6E1EF; box-shadow: 0 24px 60px rgba(15, 23, 42, 0.3); padding: 24px;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h2 style="margin: 0; font-size: 20px; font-weight: 800; color: #240E35;">🔗 Tracking Link Generator</h2>
                    <p style="margin: 4px 0 0; font-size: 14px; color: #64748B;">Create tracked links for your marketing campaigns</p>
                </div>
                <button type="button" onclick="closeUtmGenerator()" style="background: none; border: none; font-size: 24px; color: #64748B; cursor: pointer; padding: 4px;">×</button>
            </div>

            <!-- Funnel Info -->
            <div style="background: linear-gradient(135deg, #6B4A7A, #8B5A8C); color: white; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 4px;">Funnel:</div>
                <div id="utmFunnelName" style="font-size: 16px; font-weight: 700;"></div>
                <div id="utmFunnelUrl" style="font-size: 12px; opacity: 0.8; margin-top: 4px; word-break: break-all;"></div>
            </div>

            <!-- UTM Form -->
            <form id="utmGeneratorForm">
                <!-- Preset Selection -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">Quick Start</label>
                    <select id="utmPreset" onchange="applyUtmPreset()" style="width: 100%; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px; background: #fff;">
                        <option value="">Custom Setup</option>
                        <option value="facebook">Facebook Ads</option>
                        <option value="google">Google Ads</option>
                        <option value="email">Email Campaign</option>
                        <option value="instagram">Instagram Post</option>
                        <option value="youtube">YouTube Video</option>
                        <option value="linkedin">LinkedIn Campaign</option>
                    </select>
                </div>

                <!-- UTM Fields -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">Source *</label>
                        <input type="text" id="utmSource" required style="width: 100%; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px;" placeholder="facebook, google, email...">
                    </div>
                    <div>
                        <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">Medium *</label>
                        <input type="text" id="utmMedium" required style="width: 100%; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px;" placeholder="cpc, social, email...">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">Campaign Name *</label>
                    <input type="text" id="utmCampaign" required style="width: 100%; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px;" placeholder="spring_sale, new_product_launch...">
                </div>

                <!-- Optional Fields -->
                <div style="margin-bottom: 20px;">
                    <button type="button" onclick="toggleOptionalFields()" id="optionalFieldsToggle" style="background: none; border: 1px solid #E6E1EF; color: #64748B; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;">
                        + Optional Fields
                    </button>
                    <div id="optionalFields" style="display: none; margin-top: 16px;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">Content</label>
                                <input type="text" id="utmContent" style="width: 100%; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px;" placeholder="button_a, header_link...">
                            </div>
                            <div>
                                <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">Term</label>
                                <input type="text" id="utmTerm" style="width: 100%; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px;" placeholder="running_shoes, free_trial...">
                            </div>
                        </div>
                        <div style="margin-top: 16px;">
                            <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">ID</label>
                            <input type="text" id="utmId" style="width: 100%; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px;" placeholder="ad_12345, campaign_xyz...">
                        </div>
                    </div>
                </div>

                <!-- Generated URL -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 14px; font-weight: 600; color: #240E35; margin-bottom: 8px;">Generated Tracking Link</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="generatedUrl" readonly style="flex: 1; padding: 10px; border: 1px solid #E6E1EF; border-radius: 8px; font-size: 14px; background: #F8FAFC; font-family: 'Courier New', monospace;" placeholder="Fill in the form above to generate your tracking link...">
                        <button type="button" onclick="copyToClipboard()" id="copyButton" style="background: #10B981; color: white; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; white-space: nowrap;">
                            📋 Copy
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" onclick="closeUtmGenerator()" style="background: none; border: 1px solid #E6E1EF; color: #64748B; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="button" onclick="generateUtmLink()" style="background: linear-gradient(135deg, #6B4A7A, #8B5A8C); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        🚀 Generate Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="fb-modal" id="fbDeleteConfirm" aria-hidden="true">
        <div class="fb-modal-card" role="dialog" aria-modal="true" aria-labelledby="fbDeleteConfirmTitle">
            <div class="fb-modal-title" id="fbDeleteConfirmTitle">Confirm delete</div>
            <p class="fb-modal-desc" id="fbDeleteConfirmDesc">Delete this item?</p>
            <div class="fb-modal-actions">
                <button type="button" class="fb-btn" id="fbDeleteConfirmCancel">Cancel</button>
                <button type="button" class="fb-btn danger" id="fbDeleteConfirmOk">Delete</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
(function(){
    var modal=document.getElementById("fbDeleteConfirm");
    if(!modal)return;
    var desc=document.getElementById("fbDeleteConfirmDesc");
    var btnOk=document.getElementById("fbDeleteConfirmOk");
    var btnCancel=document.getElementById("fbDeleteConfirmCancel");
    var pendingForm=null;
    function closeModal(){
        modal.classList.remove("open");
        modal.setAttribute("aria-hidden","true");
        pendingForm=null;
    }
    function openModal(message,form){
        desc.textContent=message||"Delete this item?";
        pendingForm=form;
        modal.classList.add("open");
        modal.setAttribute("aria-hidden","false");
    }
    btnOk.addEventListener("click",function(){
        var form=pendingForm;
        closeModal();
        if(form)form.submit();
    });
    btnCancel.addEventListener("click",closeModal);
    modal.addEventListener("click",function(e){
        if(e.target===modal)closeModal();
    });
    document.addEventListener("keydown",function(e){
        if(!modal.classList.contains("open"))return;
        var k=String(e.key||"").toLowerCase();
        if(k==="escape")closeModal();
    });
    document.querySelectorAll("form[data-confirm-message]").forEach(function(form){
        form.addEventListener("submit",function(e){
            e.preventDefault();
            var msg=form.getAttribute("data-confirm-message")||"Delete this item?";
            openModal(msg,form);
        });
    });
})();
</script>

<!-- UTM Generator JavaScript -->
<script>
// UTM Generator JavaScript
let currentFunnel = null;

// Initialize UTM generator buttons
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers to UTM buttons
    const utmButtons = document.querySelectorAll('.utm-generator-btn');
    utmButtons.forEach(button => {
        button.addEventListener('click', function() {
            openUtmGenerator(this);
        });
    });
});

function openUtmGenerator(button) {
    currentFunnel = {
        id: button.dataset.funnelId,
        slug: button.dataset.funnelSlug,
        name: button.dataset.funnelName
    };
    
    // Populate funnel info
    document.getElementById('utmFunnelName').textContent = currentFunnel.name;
    document.getElementById('utmFunnelUrl').textContent = '{{ url('/f/') }}' + '/' + currentFunnel.slug + '/landing';
    
    // Reset form
    document.getElementById('utmGeneratorForm').reset();
    document.getElementById('generatedUrl').value = '';
    document.getElementById('optionalFields').style.display = 'none';
    document.getElementById('optionalFieldsToggle').textContent = '+ Optional Fields';
    
    // Show modal
    document.getElementById('utmGeneratorModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeUtmGenerator() {
    document.getElementById('utmGeneratorModal').style.display = 'none';
    document.body.style.overflow = '';
    currentFunnel = null;
}

function toggleOptionalFields() {
    const optionalFields = document.getElementById('optionalFields');
    const toggle = document.getElementById('optionalFieldsToggle');
    
    if (optionalFields.style.display === 'none') {
        optionalFields.style.display = 'block';
        toggle.textContent = '- Optional Fields';
    } else {
        optionalFields.style.display = 'none';
        toggle.textContent = '+ Optional Fields';
    }
}

function applyUtmPreset() {
    const preset = document.getElementById('utmPreset').value;
    const presets = {
        facebook: {
            source: 'facebook',
            medium: 'cpc',
            campaign: 'facebook_campaign_' + new Date().getTime()
        },
        google: {
            source: 'google',
            medium: 'ppc',
            campaign: 'search_campaign_' + new Date().getTime()
        },
        email: {
            source: 'newsletter',
            medium: 'email',
            campaign: 'email_campaign_' + new Date().getTime()
        },
        instagram: {
            source: 'instagram',
            medium: 'social',
            campaign: 'instagram_campaign_' + new Date().getTime()
        },
        youtube: {
            source: 'youtube',
            medium: 'video',
            campaign: 'youtube_campaign_' + new Date().getTime()
        },
        linkedin: {
            source: 'linkedin',
            medium: 'social',
            campaign: 'linkedin_campaign_' + new Date().getTime()
        }
    };
    
    if (preset && presets[preset]) {
        const data = presets[preset];
        document.getElementById('utmSource').value = data.source;
        document.getElementById('utmMedium').value = data.medium;
        document.getElementById('utmCampaign').value = data.campaign;
    }
}

function generateUtmLink() {
    // Validate required fields
    const source = document.getElementById('utmSource').value.trim();
    const medium = document.getElementById('utmMedium').value.trim();
    const campaign = document.getElementById('utmCampaign').value.trim();
    
    if (!source || !medium || !campaign) {
        alert('Please fill in all required fields (Source, Medium, Campaign)');
        return;
    }
    
    // Build UTM parameters
    const params = new URLSearchParams();
    params.append('utm_source', source);
    params.append('utm_medium', medium);
    params.append('utm_campaign', campaign);
    
    // Add optional parameters if provided
    const content = document.getElementById('utmContent').value.trim();
    const term = document.getElementById('utmTerm').value.trim();
    const id = document.getElementById('utmId').value.trim();
    
    if (content) params.append('utm_content', content);
    if (term) params.append('utm_term', term);
    if (id) params.append('utm_id', id);
    
    // Generate final URL
    const baseUrl = '{{ url('/f/') }}' + '/' + currentFunnel.slug + '/landing';
    const finalUrl = baseUrl + '?' + params.toString();
    
    // Update generated URL field
    document.getElementById('generatedUrl').value = finalUrl;
    
    // Highlight the field
    const urlField = document.getElementById('generatedUrl');
    urlField.style.borderColor = '#10B981';
    urlField.style.backgroundColor = '#F0FDF4';
    
    setTimeout(() => {
        urlField.style.borderColor = '#E6E1EF';
        urlField.style.backgroundColor = '#F8FAFC';
    }, 2000);
}

function copyToClipboard() {
    const urlField = document.getElementById('generatedUrl');
    const copyButton = document.getElementById('copyButton');
    
    if (!urlField.value) {
        alert('Please generate a link first');
        return;
    }
    
    // Copy to clipboard
    navigator.clipboard.writeText(urlField.value).then(function() {
        // Update button text
        copyButton.textContent = '✅ Copied!';
        copyButton.style.background = '#059669';
        
        // Reset after 2 seconds
        setTimeout(() => {
            copyButton.textContent = '📋 Copy';
            copyButton.style.background = '#10B981';
        }, 2000);
    }).catch(function(err) {
        // Fallback for older browsers
        urlField.select();
        document.execCommand('copy');
        
        copyButton.textContent = '✅ Copied!';
        copyButton.style.background = '#059669';
        
        setTimeout(() => {
            copyButton.textContent = '📋 Copy';
            copyButton.style.background = '#10B981';
        }, 2000);
    });
}

// Close modal when clicking outside
document.getElementById('utmGeneratorModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUtmGenerator();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('utmGeneratorModal').style.display === 'flex') {
        closeUtmGenerator();
    }
});
</script>
@endsection
