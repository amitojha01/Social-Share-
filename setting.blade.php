@extends('layouts.frontend')

@section('css_after')
<style>
.modal-backdrop {
    pointer-events: none;
}

.modal.show {
    pointer-events: none;
}

.modal-dialog {
    pointer-events: auto;
}

@keyframes li-spin { to { transform: rotate(360deg); } }
.li-spinner {
    display: inline-block;
    width: 14px; 
    height: 14px;
    border: 2px solid rgba(255,255,255,0.35);
    border-top-color: currentColor;
    border-radius: 50%;
    animation: li-spin 0.7s linear infinite;
    vertical-align: middle;
}
</style>

@endsection
@section('content')
   @php 
    $isButtonDisabled = (auth()->user()->package_id == 18) ? '' : 'disabled';
    

   @endphp

    <div class="banner bannerForAll container">
        <div id="main-banner" class="owl-carousel owl-theme">
            <div class="item shoppingCartBannaer">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="py-4">Hi {{ $first_name }}</h2>
                        <p>Your profile page, subscription details, membership points, order history, and more.</p>
                    </div>
                    <div class="col-md-1"></div>
                    <div class="col-md-5"><img class="img-fluid" src="{{asset('')}}img/ProfileBanner.png" alt="" /></div>
                </div>
            </div>
        </div>
    </div>

    <section class="main-sec">
        <div class="container">
            <!-- <div class="row">
                <div class="colProfileLeft"></div>
                <div class="colProfileRight">
                    <div class="orderRight">
                        <h3 class="userHeadding">Social Media Connection</h3>
                    </div>
                </div>
            </div> -->
            <div class="row">
                <div class="colProfileLeft">
                    @include('frontend.account.left_menu')
                </div>
                <div class="colProfileRight">
                     
                    <div class="orderRight">
                        <div class="stage">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="personalInfoForm socialmediasettings">

                                        @if(session('message'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                {{ session('message') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif

                                        @if(session('error'))
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                {{ session('error') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                            </div>
                                        @endif


                                        <div class="headingofsocialmedia">
                                            <h2>SOCIAL MEDIA CONNCTIONS</h2>
                                        </div>
                                        <div class="socialmediablocks">
                                            <div class="socialmediablcok">
                                                <div class="socialmediasettings_add">
                                                    <div class="left-section">
                                                        <div class="fb-icon">
                                                            <img src="{{asset('/img/socialfbIcon.png')}}" alt="" srcset="">
                                                        </div>
                                                        <span class="socialtitle">Connect with facebook</span>
                                                    </div>
                                                    <div class="social-connect-box">
                                                        <div class="middle-section">
                                                            <input type="text"
                                                                placeholder="Stay connected with your Facebook profile to share posts, reels & videos" value="{{  $isFacebookConnected ? 'Connected Page: ' . $facebookAccount->pluck('page_name')->implode(', ') : '' }}" readonly>
                                                        </div>

                                                        <div class="right-section">

                                                        @if($isFacebookConnected)
                                                            
                                                             <a href="{{ route('social.facebook.disconnect') }}" >
                                                            <button class="connect-btn" {{$isButtonDisabled}}>
                                                                <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> DISCONNECT
                                                            </button>
                                                             </a>
                                                             @else

                                                             <a href="{{ route('facebook.login') }}"><button class="connect-btn" {{$isButtonDisabled}}>
                                                                <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> CONNECT
                                                            </button></a>
                                                            

                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="socialmediablcok">
                                                <div class="socialmediasettings_add">
                                                    <div class="left-section">
                                                        <div class="fb-icon">
                                                            <img src="{{asset('/img/socialInstaIcon.png')}}" alt="" srcset="">
                                                        </div>
                                                        <span class="socialtitle">Connect with Instagram</span>
                                                    </div>
                                                    <div class="social-connect-box">
                                                        <div class="middle-section">
                                                            <input type="text"  @if($isInstaConnected)
                                                                    value="Connected Account: {{ $instaAccount->page_name }}"
                                                                @endif
                                                                placeholder="Stay connected with your Instagram profile to share posts, reels & videos" readonly>
                                                        </div>

                                                        <div class="right-section">
                                                            @if($isInstaConnected)
                                                            <a href="{{ route('social.instagram.disconnect') }}">
                                                                <button class="connect-btn" {{$isButtonDisabled}} >
                                                                    <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> DISCONNECT
                                                                </button>
                                                            </a>
                                                            @else 
                                                            {{--<!--<a href="{{ route('instagram.connect') }}">-->--}}
                                                                <button class="connect-btn" {{$isButtonDisabled}} onclick="instaLoginModal()" >
                                                                    <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset="" > CONNECT
                                                                </button>
                                                           {{--<!-- </a>-->--}}
                                                            @endif
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="socialmediablcok">
                                                <div class="socialmediasettings_add">
                                                    <div class="left-section">
                                                        <div class="fb-icon">
                                                            <img src="{{asset('/img/socialtwitterIcon.png')}}" alt="" srcset="">
                                                        </div>
                                                        <span class="socialtitle">Connect with Twitter X</span>
                                                    </div>
                                                    <div class="social-connect-box">
                                                        <div class="middle-section">
                                                            <input type="text"
                                                                placeholder="Stay connected with your Twitter X profile to share posts, reels & videos" readonly>
                                                        </div>

                                                        <div class="right-section">
                                                            <button class="connect-btn" {{$isButtonDisabled}} onclick="comingSoon()">
                                                                <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> CONNECT
                                                            </button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="socialmediablcok">
                                                <div class="socialmediasettings_add">
                                                    <div class="left-section">
                                                        <div class="fb-icon">
                                                            <img src="{{asset('/img/sociallinkedinIcon.png')}}" alt="" srcset="">
                                                        </div>
                                                        <span class="socialtitle">Connect with Linkedin</span>
                                                    </div>
                                                    <div class="social-connect-box">
                                                        <div class="middle-section">
                                                            <input type="text"  @if($isLinkedinConnected)
                                                                    value="Connected Profile: {{ $linkedinAccount->username  }}"
                                                                @endif 
                                                                placeholder="Stay connected with your Linkedin profile to share posts, reels & videos" readonly>
                                                        </div>
                                                        
                                                        <div class="right-section">
                                                            @if($isLinkedinConnected)
                                                            <a href="{{ route('linked-in.disconnect') }} ">
                                                            <button class="connect-btn" {{$isButtonDisabled}} >
                                                                <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> DISCONNECT
                                                            </button>
                                                            </a>
                                                            @else
                                                            <!--<a href="{{ route('linked-in.authorization') }} ">
                                            
                                                            <button class="connect-btn" {{$isButtonDisabled}} >
                                                                <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> CONNECT
                                                            </button>
                                                            </a>-->
                                                            <a href="#" id="linkedin-connect-btn" onclick="connectLinkedIn(event)">  
                                                                <button class="connect-btn" {{$isButtonDisabled}} >
                                                                    <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> CONNECT
                                                                </button>
                                                            </a>
                                                            @endif
                                                        </div>
                                                        

                                                    </div>
                                                </div>
                                            </div>

                                            {{--<!--<div class="socialmediablcok">
                                                <div class="socialmediasettings_add">
                                                    <div class="left-section">
                                                        <div class="fb-icon">
                                                            <img src="{{asset('/img/socialtelegramIcon.png')}}" alt="" srcset="">
                                                        </div>
                                                        <span class="socialtitle">Connect with Telegram </span>
                                                    </div>
                                                    <div class="social-connect-box">
                                                        <div class="middle-section">
                                                            <input type="text"
                                                                placeholder="Stay connected with your Telegram profile to share posts, reels & videos">
                                                        </div>

                                                        <div class="right-section">
                                                            <button class="connect-btn" {{$isButtonDisabled}} onclick="comingSoon()">
                                                                <img src="{{asset('/img/socialConnectIcon.png')}}" alt="" srcset=""> CONNECT
                                                            </button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>-->--}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </section>

   <div class="modal fade" id="instagramModal" tabindex="-1" role="dialog" aria-labelledby="igModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius:16px; border:none; overflow:hidden;">

            <div class="modal-header" style="border-bottom:1px solid #f0f0f0; padding:1.25rem 1.5rem;">
                <div class="d-flex align-items-center gap-2">
                <div style="width:36px;height:36px;border-radius:9px;background:#cc2366;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fab fa-instagram" style="color:#fff;font-size:18px;"></i>
                </div>
                <div style="margin-left:10px">
                    <h5 class="modal-title mb-0" id="igModalTitle" style="font-size:15px;font-weight:600;">Connect Instagram</h5>
                    <small class="text-muted">Link your business account</small>
                </div>
                </div>         
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" style="padding:1.25rem 1.5rem;">

                <div class="alert alert-info d-flex gap-2 align-items-start py-2 px-3 mb-4" style="font-size:13px; border-radius:8px;">
                <i class="fas fa-info-circle mt-1" style="flex-shrink:0;"></i>
                <span style="margin-left:10px">You must be logged into Instagram in this browser before connecting to prevent failures.</span>
                </div>

                <div class="steps mb-4">

                    <div class="d-flex gap-3 position-relative" id="stepRow1">
                        
                        <div class="d-flex flex-column align-items-center" style="flex-shrink:0;">
                            <div id="stepCircle1"  style="width:28px;height:28px;border-radius:50%;border:1px solid #dee2e6;background:#f8f9fa; display:flex;align-items:center;justify-content:center;      font-size:12px;font-weight:600;color:#6c757d;flex-shrink:0;">
                                1
                            </div>
                             <div style="width:1px;flex:1;min-height:40px;background:#dee2e6;margin-top:4px;"></div>
                        </div>

                        <div style="padding-bottom:1.25rem;">
                            <strong style="font-size:14px;display:block;margin-bottom:3px;">Login to Instagram first</strong>
                            <p class="text-muted mb-2" style="font-size:13px;line-height:1.5;">
                                Open Instagram and sign in to your account in this browser.
                            </p>
                            <a href="https://www.instagram.com" target="_blank"
                            onclick="markStepDone()"
                            class="btn btn-sm btn-outline-secondary"
                            style="font-size:12px;padding:4px 10px;border-radius:6px;">
                                <i class="fab fa-instagram me-1"></i> Open Instagram
                                <i class="fas fa-external-link-alt ms-1" style="font-size:10px;"></i>
                            </a>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="d-flex flex-column align-items-center" style="flex-shrink:0;">
                            <div id="stepCircle2"   {{-- ← add this id --}}
                                style="width:28px;height:28px;border-radius:50%;border:1px solid #dee2e6;background:#f8f9fa;
                                        display:flex;align-items:center;justify-content:center;
                                        font-size:12px;font-weight:600;color:#6c757d;">
                                2
                            </div>
                        </div>
                        <div>
                            <strong style="font-size:14px;display:block;margin-bottom:3px;">Come back and confirm</strong>
                            <p class="text-muted mb-0" style="font-size:13px;line-height:1.5;">
                                Check the box below once logged in, then click Connect.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-3" style="background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef;">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="igConfirm" onchange="toggleIgBtn(this)">
                    <label class="form-check-label" for="igConfirm" style="font-size:13px;cursor:pointer;">
                    I am already logged into Instagram in this browser
                    </label>
                </div>
                </div>

            </div>

            <div class="modal-footer flex-column gap-2" style="padding:1rem 1.5rem;border-top:1px solid #f0f0f0;">
                <a href="{{ route('instagram.connect') }} "
                    id="igConnectBtn"
                    class="btn w-100 disabled"
                    style="background:#cc2366;color:#fff;border:none;border-radius:8px;
                            font-size:14px;font-weight:500;padding:.7rem;
                            pointer-events:none; opacity:0.4;">   {{-- starts faded --}}
                    <i class="fab fa-instagram me-2"></i> Connect Instagram now
                </a>
                <a href="{{ url()->previous() }}">
                <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal"
                        style="font-size:13px;border-radius:8px;">
                Cancel
                </button>
                </a>
            </div>

            <p class="text-center text-muted pb-3" style="font-size:12px;">
                <i class="fas fa-lock me-1"></i> Your credentials are never stored by us
            </p>

            </div>
        </div>
    </div>

        
    <div id="shareValidationModal" 
        class="modal fade" 
        tabindex="-1" 
        role="dialog"
        data-backdrop="static"
        data-keyboard="false">

        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Upgrade Alert</h5>
                </div>

                <div class="modal-body">
                <p id="success_model_body" style="text-align: center;font-size: 16px;">Kindly upgrade your membership to Diamond plan to access this feature.</p>
                </div>

                <div class="modal-footer text-center" style="justify-content: center;">
                
                <a href="{{route('account.upgradePackage')}}" type="button" class="btn btn-secondary btnblue" style="background: #141f55;padding: 0.5rem 2rem;border-radius: 1.5rem;">Upgrade Now</a>
            </div>

            </div>
        </div>
    </div>


    <div class="modal fade" id="comingSoon" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Coming Soon Alert</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p id="success_model_body" style="text-align: center;font-size: 16px;">🚀 Exciting feature coming soon! Stay tuned for updates.</p>
                </div>
                <div class="modal-footer text-center" style="justify-content: center;">
                    <button type="button" style="background: #141f55;padding: 0.5rem 2rem;border-radius: 1.5rem;" class="btn btn-secondary btnblue" data-dismiss="modal">Ok</button>
                
                </div>
            </div>
        </div>
    </div>


@endsection
@section("js_after")
<script>
$(document).ready(function(){ 
    var package_id= {{ auth()->user()->package_id }};
    if(package_id!=18){ //if not diamond user        
        $('#shareValidationModal').modal('show'); // Show the Bootstrap modal
    }
}); 

function comingSoon(){
    $('#comingSoon').modal('show');
}


function instaLoginModal(){    
    $('#instagramModal').modal('show');
}

$(document).ready(function () {

    // Toggle connect button when checkbox changes
    $('#igConfirm').on('change', function () {
        var btn = $('#igConnectBtn');
        if ($(this).is(':checked')) {
            // Enable connect button
            btn.removeClass('disabled')
               .css({ 'opacity': '1', 'pointer-events': 'auto' });

            // Mark step 2 as done
            $('#stepCircle2')
                .css({ 'background': '#d1fae5', 'border-color': '#6ee7b7', 'color': '#065f46' })
                .html('<i class="fas fa-check" style="font-size:11px;"></i>');

        } else {
            // Disable connect button
            btn.addClass('disabled')
               .css({ 'opacity': '0.45', 'pointer-events': 'none' });

            // Reset step 2 circle
            $('#stepCircle2')
                .css({ 'background': '#f8f9fa', 'border-color': '#dee2e6', 'color': '#6c757d' })
                .html('2');
        }
    });

    // Mark step 1 done when Open Instagram is clicked
    $('a[href="https://www.instagram.com"]').on('click', function () {
        markStepDone();
    });

    // Connect button click — show spinner
    $(document).on('click', '#igConnectBtn', function () {
        if (!$(this).hasClass('disabled')) {
            $(this).html('<span class="spinner-border spinner-border-sm me-2"></span> Connecting...')
                   .css({ 'opacity': '0.7', 'pointer-events': 'none' });
        }
    });

    // Reset modal state when closed
    $('#instagramModal').on('hidden.bs.modal', function () {
        $('#igConfirm').prop('checked', false);

        $('#igConnectBtn')
            .addClass('disabled')
            .css({ 'opacity': '0.4', 'pointer-events': 'none' })
            .html('<i class="fab fa-instagram me-2"></i> Connect Instagram now');

        // Reset step 1 circle
        $('#stepCircle1')
            .css({ 'background': '#f8f9fa', 'border-color': '#dee2e6', 'color': '#6c757d' })
            .html('1');

        // Reset step 2 circle
        $('#stepCircle2')
            .css({ 'background': '#f8f9fa', 'border-color': '#dee2e6', 'color': '#6c757d' })
            .html('2');
    });

});

function markStepDone() {
    $('#stepCircle1')
        .css({ 'background': '#d1fae5', 'border-color': '#6ee7b7', 'color': '#065f46' })
        .html('<i class="fas fa-check" style="font-size:11px;"></i>');
}



function connectLinkedIn(e) {
    e.preventDefault();
    
    const $btn = $('#linkedin-connect-btn');
    const authUrl = '{{ route("linked-in.authorization") }}';
    
    $btn.prop('disabled', true)
        .html('<span class="li-spinner"></span>&nbsp; Connecting...');

    const logoutPopup = window.open(
        'https://www.linkedin.com/m/logout',
        'linkedin_logout',
        // ✅ Position exactly behind current window
        `width=1,height=1,` +
        `left=${window.screenX + window.outerWidth},` +  // just outside right edge
        `top=${window.screenY + window.outerHeight}`     // just outside bottom edge
    );

    if (!logoutPopup || logoutPopup.closed || typeof logoutPopup.closed === 'undefined') {
        window.location.href = authUrl;
        return;
    }

    // ✅ This is the key — send popup to background immediately
    logoutPopup.blur();
    window.focus();

    let redirected = false;

    const doRedirect = () => {
        if (redirected) return;
        redirected = true;
        try { logoutPopup.close(); } catch(err) {}
        window.location.href = authUrl;
    };

    logoutPopup.onload = () => {
        try { logoutPopup.blur(); window.focus(); } catch(err) {}
        setTimeout(doRedirect, 500);
    };

    const pollTimer = setInterval(() => {
        try {
            // Keep forcing focus back to main window every poll
            if (document.activeElement) window.focus();
            if (logoutPopup.closed) {
                clearInterval(pollTimer);
                doRedirect();
            }
        } catch(err) {
            clearInterval(pollTimer);
            setTimeout(doRedirect, 800);
        }
    }, 300);

    setTimeout(() => {
        clearInterval(pollTimer);
        doRedirect();
    }, 5000);
}

//working fine need to modify
/*
function connectLinkedIn(e) {
    e.preventDefault();
    
    const $btn = $('#linkedin-connect-btn');
    const authUrl = '{{ route("linked-in.authorization") }}';
    
    // ✅ Show spinner — matches your existing button style
    $btn.prop('disabled', true)
        .html('<span class="li-spinner"></span>&nbsp; Connecting...');


    // Open LinkedIn logout in popup
    const logoutPopup = window.open(
        'https://www.linkedin.com/m/logout',
        'linkedin_logout',
        'width=1,height=1,left=-9999,top=-9999' // Invisible off-screen
    );

    // Popup blocked fallback
    if (!logoutPopup || logoutPopup.closed || typeof logoutPopup.closed === 'undefined') {
        window.location.href = authUrl;
        return;
    }

    // ✅ Detect when LinkedIn logout page has fully loaded
    let redirected = false;

    const doRedirect = () => {
        if (redirected) return; // ✅ Prevent double redirect
        redirected = true;
        try { logoutPopup.close(); } catch(err) {}
        window.location.href = authUrl;
    };

    // ✅ Method 1: onload fires when logout page finishes loading
    logoutPopup.onload = () => {
        setTimeout(doRedirect, 500); // Small delay after load completes
    };

    // ✅ Method 2: Poll until popup has loaded linkedin.com (cross-origin safe check)
    const pollTimer = setInterval(() => {
        try {
            // If popup closed itself
            if (logoutPopup.closed) {
                clearInterval(pollTimer);
                doRedirect();
                return;
            }
        } catch(err) {
            // Cross-origin error means page has loaded (LinkedIn domain)
            clearInterval(pollTimer);
            setTimeout(doRedirect, 800); // Wait 800ms after LinkedIn loads
        }
    }, 300);

    // ✅ Method 3: Hard fallback — max wait 5s no matter what
    setTimeout(() => {
        clearInterval(pollTimer);
        doRedirect();
    }, 5000);
}*/


</script>

@endsection