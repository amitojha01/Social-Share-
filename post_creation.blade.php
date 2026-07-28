@extends('layouts.frontend')
<!-- Head -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@section('js_after')
<script>

    // JS - just one line
    flatpickr("#dateTime", {
        enableTime: true,
        dateFormat: "d - m - Y h - i K",
        allowInput: true // allows manual editing
    });

    $(document).ready(function () {
        $('#pageId').select2({
            placeholder: "Select Page",
            width: '100%'
        });
    });
</script>

@endsection
<style>
    
/* Warning Icon */
.warning-icon img {
    width: 85px;
    margin-bottom: 15px;
}

/* Heading */
.social-popup-content h2 {
    font-size: 30px;
    font-weight: 700;
    color: #d76f2d;
    line-height: 1.4;
    margin-bottom: 15px;
    
}

/* Description */
.popup-text {
    color: #f48c8c;
    font-size: 14px;
    /*line-height: 1.6;*/
    margin-bottom: 25px;
    font-weight: 700;
    padding:0px 20px 10px 20px; 
}

</style> 

@section('content')
<!-----HTML Section--->
<section class="main-sec">
    <div class="container">
        {{--<form id="publishForm" method="POST" action="{{ route('facebook.publish') }}">--}}
        <form id="publishForm">
            @csrf
          
            @if($shareType=='video')
            <input type="hidden" value="{{$video_slug}}" name='video_slug'>
            <input type="hidden" value="{{$video_type}}" name='video_type'>
            @endif
            <div class="shareaarea">
                <div class="personalInfoForm">
                    <div class="headingofsocialmedia">                        
                        @if($shareOn=='facebook')
                        <h2><img class="fbIconheading" src="{{asset('/img/socialfbIcon.png')}}" alt="" srcset=""> SHARE IN
                            FACEBOOK </h2>
                        @elseif($shareOn=='instagram')
                         <h2><img class="fbIconheading" src="{{asset('/img/socialInstaIcon.png')}}" alt="" srcset=""> SHARE IN
                            INSTAGRAM</h2>                        
                         @elseif($shareOn=='linkedin')
                         <h2><img class="fbIconheading" src="{{asset('/img/sociallinkedinIcon.png')}}" alt="" srcset=""> SHARE IN LINKEDIN</h2>
                        @endif
                    </div>
                    <div class="allsocialmeadiablock">
                        <div class="socialmediaformblock forattachmedia">
                            <label for="">Attached Media : </label>
                            <div style="text-align:center; width: calc(100% - 130px);"><img
                                    src="{{ $banner_url }}" alt="" srcset="" style='width:110px;height:130px'></div>
                            <input type="hidden" name="banner_url" value="{{ $banner_url }}">
                        </div>
                    </div>
                    <div class="allsocialmeadiablock">
                        <div class="socialmediaformblock">
                            <label for="">Caption & Text Message for Post</label>
                            <div><input type="text" name="text_message" placeholder="Please put your caption text here" id=""></div>
                        </div>
                    </div>
                    <div class="allsocialmeadiablock">
                        <div class="socialmediaformblock">
                            <label for="">Description for Post</label>
                            <div><textarea placeholder="Please put your post description here" name="description"></textarea></div>
                        </div>
                    </div>
                    {{--<!--<div class="allsocialmeadiablock twocolumn">
                        <div class="socialmediaformblock">
                            <label for="">CTA Link / Supportive URL</label>
                            <div><input type="text" placeholder="Please put your caption text here"></div>
                        </div>
                        <div class="socialmediaformblock">
                            <label for="">Permissions / Privacy</label>
                            <div>
                                <select name="" id="">
                                    <option value="">Global / Public</option>
                                    <option value="">Global 1</option>
                                    <option value="">Global 2</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="allsocialmeadiablock twocolumn">
                        <div class="socialmediaformblock">
                            <label for="">Scheduled Date & Time</label>
                            <div><input type="text" placeholder="DD - MM - YYYY HH - MM  AM/PM" class="sociallink"></div>
                        </div>
                        <div class="socialmediaformblock">
                            <label for="">Publishing Location</label>
                            <div>
                                <input type="text" placeholder="Salt Lake , Kolkata" class="sociallocation">
                            </div>
                        </div>
                    </div>-->--}}

                    <!-----Schedule and Location--->
                     @if($shareOn=='facebook')
                    <div class="allsocialmeadiablock twocolumn"> 
                       
                        <div class="socialmediaformblock">
                            <label for="">Scheduled Date & Time</label>
                            <div>
                                <input type="text" placeholder="DD - MM - YYYY HH - MM  AM/PM" class="sociallink" id="dateTime" name="schedule_datetime">
                                
                            </div>
                        </div>                                            
                        <div class="socialmediaformblock">
                            <label for="">Publishing Location</label>
                            <div style="position: relative;">
                                <input 
                                    type="text" 
                                    id="locationSearch"
                                    name="location_name"
                                    placeholder="Salt Lake, Kolkata" 
                                    class="sociallocation sociallink"
                                    autocomplete="off" 
                                />
                                 <!-- Hidden fields -->
                                <!--<input type="text" id="locationLat" name="location_lat" />
                                <input type="text" id="locationLng" name="location_lng" />

                                <ul id="locationSuggestions" style="display:none;"></ul>
                                    -->
                                
                            </div>
                        </div>
                    </div>
                    @endif
                   
                    <div class="allsocialmeadiablock">
                        <label for="">Tags</label>

                        <div class="socialtagsarea form-control d-flex flex-wrap" id="tagWrapper">         
                            <input type="text" id="tagInput" style="border:none; outline:none; flex:1;" placeholder="Type and press Enter">                            
                        </div> 
                        <input type="hidden" name="tags" id="tagsInput">                   
                    </div>

                    @if($shareOn=='facebook')
                    <div class="socialmediaformblock">
                        <label for="">Page</label>
                        <div>                                
                            <select name="page_id[]" id="pageId" multiple>
                                @foreach($fbData as $data)
                                    <option value="{{ $data->page_id }}">{{ $data->page_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endif                    
                    {{--<!--<div class="allsocialmeadiablock">
                        <div class="socialmediaformblock">
                            <div class="socialtagsarea">
                                <label class="socialmedia-custom-radio">
                                    <input type="radio" name="share" checked>
                                    <span class="radio-mark"></span>
                                    Share in Instagram
                                </label>

                                <label class="socialmedia-custom-radio">
                                    <input type="radio" name="share">
                                    <span class="radio-mark"></span>
                                    Share in Twitter X
                                </label>

                                <label class="socialmedia-custom-radio">
                                    <input type="radio" name="share">
                                    <span class="radio-mark"></span>
                                    Share in Telegram
                                </label>

                                <label class="socialmedia-custom-radio">
                                    <input type="radio" name="share">
                                    <span class="radio-mark"></span>
                                    Share in Linkedin
                                </label>
                            </div>
                        </div>
                    </div>-->--}}
                    <div class="allsocialmeadiablock">
                        <div class="allbtnarea">
                            <a href="{{ route('frontend.premiumbanner.index') }}"><button class="socialborderBtn"><img class="socialbackbtn"
                                    src="{{asset('/img/socialbackbtn.png')}}" alt="" srcset=""> BACK</button></a>
                            <button class="socialborderBtn">CANCEL</button>
                            <button type="submit" class="socialborderBtn socialsolidbtn" id="publishBtn">PUBLISH</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
<div id="attentionPopupModal" class="popup-overlay">
    <div class="social-popup-content">
        <span class="close-btn" onclick="closePopup()">&times;</span>
        <div class="warning-icon"><img src="{{asset('/img/attention.gif')}}" alt="" srcset=""></div>
        <span id="modal_content">
            <h2 id="popup_title" style="color: #ca8942">No Connected Facebook <br>Account / Page(s) are found.</h2>  <p class="popup-text">
            Kindly connect your Facebook pages first to share post
            with your preferred branding & necessary captions.
        </p>
        </span>
        <div class="socialprofileimg"><img src="{{ $banner_url }} "  style='width:70px;height:90px' alt="" srcset=""></div>
        <a href="{{ route('frontend.premiumbanner.index') }}">
            <button class="socialborderBtn" style="margin: 30px 14px 30px 5px; ">
                <img class="socialbackbtn" src="{{asset('/img/socialbackbtn.png')}}" alt="" srcset=""> BACK TO LIST 
            </button>
        </a>

        <a href="{{ route('social.setting') }}"><button class="socialborderBtn" id='connectbtn'><i class="fa fa-facebook"></i>CONNECT FACEBOOK</button></a>
    </div>
</div>

<div id="successPopupModal" class="popup-overlay">
    <div class="social-popup-content">
        <span class="close-btn" onclick="closePopup()">&times;</span>
        <div class="warning-icon"><img src="{{asset('/img/socialRight.gif')}}" alt="" srcset=""></div>
        <h2 id="shareInfo"></h2>  
        <div class="socialprofileimg"><img src="{{ $banner_url }} "  style='width:70px;height:90px' alt="" srcset=""></div>
        <a href="{{ route('frontend.premiumbanner.index') }}">
            <button class="socialborderBtn">
                <img class="socialbackbtn" src="{{asset('/img/socialbackbtn.png')}}" alt="" srcset=""> BACK TO LIST 
            </button>
        </a>        
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   /* function openPopup() {
        document.getElementById("popupModal").style.display = "flex";
    }*/
    function closePopup() {
        document.getElementById("attentionPopupModal").style.display = "none";
        document.getElementById("successPopupModal").style.display = "none";
      
    }

    // Optional: close when clicking outside
    window.onclick = function (e) {
        let modal = document.getElementById("attentionPopupModal");
        if (e.target === modal) {
            closePopup();
        }
    }

 
    //Publish banner function
   $('#publishForm').on('submit', function(e) {
        e.preventDefault(); 
        //var share_type= $('#shareType').val();        
        var shareOn = '{{ $shareOn ?? "" }}'; 
        var share_type = '{{ $shareType ?? "" }}'; 
        //alert(shareOn);
        //alert(share_type);
        //let modalId = 'attentionPopupModal'; // default
                
        var publishUrl = '';

        if (shareOn === 'facebook') {
            publishUrl = (share_type === 'banner')
                ? "{{ route('facebook.publish') }}"
                : "{{ route('facebook.publish.video') }}";
        } else if (shareOn === 'instagram') {           
            //publishUrl = "{{ route('insta.publish') }}";
           publishUrl = (share_type === 'banner')
                ? "{{ route('insta.publish') }}"
                : "{{ route('insta.publish.video') }}";
            $('#modal_content').html(`
                    <h2>
                        No Connected Instagram <br>
                        Account are found.
                    </h2>
                    <p class="popup-text">
                        Kindly connect your Instagram first to share posts
                        with your preferred branding &amp; necessary captions.
                    </p>
                `);
            $('#connectbtn').text('INSTAGRAM CONNECT');
        }
        else if (shareOn === 'linkedin') {
            $('#modal_content').html(`
                    <h2>
                        No Connected Linkedin <br>
                        Account are found.
                    </h2>
                    <p class="popup-text">
                        Kindly connect your Linkedin first to share posts
                        with your preferred branding &amp; necessary captions.
                    </p>
                `);
                $('#connectbtn').text('LINKEDIN CONNECT');
                     
           // publishUrl = "{{ route('linkedin.publish') }}";
            publishUrl = (share_type === 'banner')
                ? "{{ route('linkedin.publish') }}"
                : "{{ route('linkedin.publish.video') }}";
        }
        //alert(publishUrl);                     

        let form = document.getElementById('publishForm');
        let formData = new FormData(form); // automatically gets all fields
        console.log(...formData); 
        
         //Change button state
        let btn = $('#publishBtn');
        btn.prop('disabled', true);
        btn.text('Publishing...');

        console.log('shareOn:', shareOn);
        console.log('share_type:', share_type);
        console.log('publishUrl:', publishUrl);


        $.ajax({           
            //url: share_type == 'banner' ? "{{ route('facebook.publish') }}" : "{{ route('facebook.publish.video') }}",
            //url: "{{ route('insta.publish') }}", //for insta test
            url: publishUrl,
            type: "POST",
            data: formData,
            processData: false, // ❗ required
            contentType: false, // ❗ required
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                console.log(response);
                if(response.success) {
                    $('#shareInfo').text( response.message +'🎉');
                    document.getElementById("successPopupModal").style.display = "flex";
               
                }else{
                    if(response.type=='schedule') {
                        $('#popup_title').text('Invalid Schedule Time');                        
                        $('.popup-text').text(response.message);
                        $('#connectbtn').hide();
                    }
                   // $('#shareInfo').text('No Connected Facebook Account / Page(s) are found. Please connect first to share post with your preferred branding & necessary captions.');
                   document.getElementById("attentionPopupModal").style.display = "flex";
                }
                 // Restore button
                btn.prop('disabled', false);
                btn.text('PUBLISH');

               // document.getElementById("attentionPopupModal").style.display = "flex";
                
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert('Oops!! Something went wrong');
            }
        });
    });
    //Tag functionality implementation    
    let tags = [];

    // Add tag
    $('#tagInput').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();

            let tag = $(this).val().trim().replace('#', '');

            if (tag !== '' && !tags.includes(tag)) {
                tags.push(tag);
                renderTags();
            }

            $(this).val('');
        }
    });

    // Render Tags
    function renderTags() {
        let html = '';

        tags.forEach((tag, index) => {
            html += `
                <span style="background:#eee; padding:5px 10px; margin:2px; border-radius:15px;">
                    #${tag}
                    <a href="javascript:void(0)" onclick="removeTag(${index})" style="margin-left:5px;">
                        <img src="{{ asset('/img/socialCrossIcon.png') }}" width="10">
                    </a>
                </span>
            `;
        });

        // Append input at end
    
        // input always at end
        html += `<input type="text" id="tagInput" placeholder="Type and press Enter">`;

        $('#tagWrapper').html(html);

        // Re-bind input event (IMPORTANT)
        bindInput();

        // Store JSON
        $('#tagsInput').val(JSON.stringify(tags));
    }

    // Rebind input after render
    function bindInput() {
        $('#tagInput').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();

                let tag = $(this).val().trim().replace('#', '');

                if (tag !== '' && !tags.includes(tag)) {
                    tags.push(tag);
                    renderTags();
                }
            }
        });
    }

    // Remove Tag
    function removeTag(index) {
        tags.splice(index, 1);
        renderTags();
    }

</script>
@endsection
