<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\UserSocialAccount;
use Illuminate\Support\Facades\Auth;
use Image;
use App\Models\SocialPostHistory;
use App\Models\Reelvideo;
use App\Http\Helpers\FrontHelper;
use App\Http\Helpers\SocialMediaHelper;
use App\Models\Marketingvideo;
use Illuminate\Support\Facades\Storage;


class SocialController extends Controller
{
    /**
     * SocialController
     * Function name : redirectToFacebook()
     * Purpose       : Redirect the user to Facebook's authentication page
     *                 and request the required permissions for accessing
     *                 profile information and managing Facebook Pages.
     * Params        : None
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')
            ->scopes([
                'public_profile',
                'email',
                'pages_show_list',
                'pages_manage_posts',
                'pages_read_engagement',
                
            ])
            /*->with([
                'auth_type' => 'rerequest',  // ← Forces fresh permission dialog
                'display' => 'popup'
            ])*/
            ->redirect();
    }

    /**
     * SocialController
     * Function name : handleFacebookCallback()
     * Purpose       : Handle Facebook OAuth callback, retrieve the user's
     *                 Facebook pages and access tokens, save/update page
     *                 details in the database, and connect the Facebook
     *                 account for future social media publishing.
     * Params        : None
     */

    public function handleFacebookCallback()
    {
        $fbUser = Socialite::driver('facebook')->stateless()->user();

        // Step 1: Get user token
        $userToken = $fbUser->token;

        // Step 2: Get pages
        $pages = Http::get("https://graph.facebook.com/v23.0/me/accounts", [
            'access_token' => $userToken
        ])->json();

        // Step 3: Select first page (page logic)
        if (!empty($pages['data']) && isset($pages['data'][0])) {
            $page = $pages['data'][0];
        } else {
            return redirect()->route('social.setting')->with('error', 'No Facebook pages found.');
            //return "No Facebook pages found.";
        }
        
        if (!empty($pages['data'])) {

            foreach ($pages['data'] as $page) {

                UserSocialAccount::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'provider' => 'fb',
                        'page_id' => $page['id'], // ✅ unique per page
                    ],
                    [
                        'provider_user_id' => $fbUser->id,
                        'access_token' => $userToken,
                        'page_name' => $page['name'],
                        'page_token' => $page['access_token']
                    ]
                );
            }
        } else {
            return redirect()->route('social.setting')
                ->with('error', 'No Facebook pages found.');
        }

        return redirect()->route('social.setting')
            ->with('message', 'Facebook connected successfully.');

    }
        
    
    /**
     * SocialController
     * Function name : socialSetting()
     * Purpose       : Load the Social Media Settings page by retrieving
     *                 the connection status and account details of
     *                 Facebook, Instagram, and LinkedIn accounts for
     *                 the logged-in user.
     * Params        : None
     */

    public function socialSetting()
    {
        $left_menu = "social_share";
        
        $isFacebookConnected = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'fb')
            ->exists();

        $facebookAccount = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'fb')
            ->get();

        $data['isInstaConnected'] = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'insta')->where('token_expires_at', '>', now())
            ->exists();

        $data['instaAccount'] = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'insta')->where('token_expires_at', '>', now())
            ->first();

        $data['isLinkedinConnected'] = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'linkedin')->where('token_expires_at', '>', now())
            ->exists();

        $data['linkedinAccount'] = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'linkedin')->where('token_expires_at', '>', now())
            ->first();

        $data['first_name'] = auth()->user()->first_name;         

        return view('frontend.social.setting', compact(
            'left_menu',
            'isFacebookConnected',
            'facebookAccount'
        ), $data);
    }

    /**
     * SocialController
     * Function name : disconnectFacebook()
     * Purpose       : Disconnect all connected Facebook accounts by
     *                 revoking Facebook app permissions and removing
     *                 stored Facebook account details from the database.
     * Params        : None
    */
    public function disconnectFacebook()
    {
        try {             
            $socials = UserSocialAccount::where('user_id', Auth::id())
                ->where('provider', 'fb')
                ->get();

            if ($socials->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Facebook account connected.'
                ]);
            }

            foreach ($socials as $social) {
                // Step 1: Revoke Facebook App Permission
                Http::delete("https://graph.facebook.com/v23.0/{$social->provider_user_id}/permissions", [
                    'access_token' => $social->access_token
                ]);

                // Step 2: Delete from Database
                $social->delete();
            }

            return redirect()->route('social.setting')
                ->with('message', 'All Facebook accounts disconnected successfully.');
        } catch (\Exception $e) {
            return redirect()->route('social.setting')
                ->with('error', 'Failed to disconnect Facebook: ' . $e->getMessage());
        }
    }

    
    /**
     * SocialController
     * Function name : showPostCreationForm()
     * Purpose       : Prepare and load the social media post creation
     *                 page for Facebook or instagram by generating the banner URL and fetching
     *                 connected Facebook page details when required.
     * Params        : $shareon, $banner
     */
    public function showPostCreationForm($shareon, $banner)
    {       
        $banner_url = config('filesystems.s3_base_url') . '/public/uploads/premiumbanner/premium/' . $banner;
        $shareType= 'banner';
        $fbData = '';
        if($shareon == 'facebook'){
            $fbData = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'fb')
            ->get();
        }
        $data['shareOn'] = $shareon;
        return view('frontend.social.post_creation', compact('banner_url', 'fbData', 'shareType'), $data);
    }


    /**
     * SocialController
     * Function name : publishBanner()
     * Purpose       : Publish or schedule a branded banner image post
     *                 on one or more connected Facebook Pages, save the
     *                 post details in publish history, and remove the
     *                 temporary banner image after processing.
     * Params        : Request $request
     */
       
    public function publishBanner(Request $request)
    {
        $imageUrl = $request->banner_url;
        $banner = FrontHelper::bannerBranding($imageUrl);
        $banner_url = $banner['url'];
        //$banner_url = $request->banner_url; // For Local host test also need to remove delete banner
   
        $page_id = $request->page_id;
        $text_message = $request->text_message;
        $tags = json_decode($request->tags, true);
        $description = $request->description;
        // Schedule datetime from request
        $schedule_datetime = $request->schedule_datetime;
    
        // Convert tags to hashtags
        $tagString = '';
        if (!empty($tags)) {
            $tagString = ' #' . implode(' #', $tags);
        }

        // Final message (caption + description + tags)
        $finalMessage = $text_message . "\n\n" . $description . "\n\n" . $tagString; 

        // Append location to message instead of tagging
        $locationName = $request->location_name;
        if (!empty($locationName)) {
           $finalMessage .= "\n\n📍 " . $locationName;
        }

        // Get ALL pages
        $socialAccounts = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'fb')
            ->when(!empty($page_id), function ($query) use ($page_id) {
                //$query->where('page_id', $page_id);
                if (is_array($page_id)) {
                    $query->whereIn('page_id', $page_id);
                } else {
                    $query->where('page_id', $page_id);
                }
            })
            ->get();

        //Check if empty
        if ($socialAccounts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook not connected',
                'type'    => 'not_connected',
            ]);
        }
        $responses = [];

        foreach ($socialAccounts as $account) {
            $pageId    = $account->page_id;
            $pageToken = $account->page_token;
            
             $payload = [
                'message' => $finalMessage,
                'url' => $banner_url,
                'access_token' => $pageToken,
            ];

            /*if (!empty($schedule_datetime)) {
                preg_match('/(\d{2})\s*-\s*(\d{2})\s*-\s*(\d{4})\s+(\d{2})\s*-\s*(\d{2})\s*(AM|PM)/i', $schedule_datetime, $matches);

                //dd($matches);

                $formatted = "{$matches[3]}-{$matches[2]}-{$matches[1]} {$matches[4]}:{$matches[5]} {$matches[6]}";

                $payload['published'] = false;
                $payload['scheduled_publish_time'] = \Carbon\Carbon::createFromFormat(
                    'Y-m-d h:i A',
                    $formatted,
                    'Asia/Kolkata'  // ✅ correct timezone
                )->timestamp;
            }*/

            
            $scheduledCarbon = null;
            if (!empty($schedule_datetime)) {
                preg_match(
                    '/(\d{1,2})\s*-\s*(\d{1,2})\s*-\s*(\d{4})\s+(\d{1,2})\s*-\s*(\d{1,2})\s*(AM|PM)/i',
                    $schedule_datetime,
                    $matches
                );

                if (empty($matches)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid schedule datetime format',
                        'type'    => 'schedule'
                    ]);
                }

                $hour = (int) $matches[4];
                $ampm = strtoupper($matches[6]);

                if ($ampm === 'AM' && $hour === 12) $hour = 0;
                elseif ($ampm === 'PM' && $hour !== 12) $hour += 12;

                $formatted = sprintf(
                    '%s-%s-%s %s:%s:00',
                    $matches[3],                            // year
                    str_pad($matches[2], 2, '0', STR_PAD_LEFT), // month
                    str_pad($matches[1], 2, '0', STR_PAD_LEFT), // day
                    str_pad($hour,       2, '0', STR_PAD_LEFT), // hour (24hr)
                    str_pad($matches[5], 2, '0', STR_PAD_LEFT)  // minute
                );
                // "19 - 05 - 2026 11 - 05 PM" → "2026-05-19 23:05:00"

                $scheduledCarbon = \Carbon\Carbon::createFromFormat(
                    'Y-m-d H:i:s',
                    $formatted,
                    'Asia/Kolkata'
                );

                // Facebook: must be 10 mins in future
                if ($scheduledCarbon->timestamp < now('Asia/Kolkata')->addMinutes(10)->timestamp) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled time must be at least 10 minutes in the future',
                        'type'    => 'schedule'
                    ]);
                }

                // Facebook: max 30 days ahead
                if ($scheduledCarbon->timestamp > now('Asia/Kolkata')->addDays(30)->timestamp) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Scheduled time cannot be more than 30 days in the future',
                        'type'    => 'schedule'
                    ]);
                }

                //Use in Facebook payload
                if ($scheduledCarbon) {
                    $payload['published']              = false;
                    $payload['scheduled_publish_time'] = $scheduledCarbon->timestamp;
                }
            }

            $response = Http::post(
                "https://graph.facebook.com/v19.0/$pageId/photos",
                $payload
            );

            $responseData = $response->json();

            $responses[] = [
                'page_id' => $pageId,
                //'response' => $response->json()
                'response' => $responseData                
            ];

            /* Save publish history */
            SocialPostHistory::create([
                'user_id'         => Auth::id(),
                'provider'        => 'fb',
                'page_id'         => $pageId,
                'page_name'       => $account->page_name ?? null,
                'banner_url'      => $banner_url,
                'message'         => $finalMessage,
                'facebook_post_id'=> $responseData['post_id'] ?? ($responseData['id'] ?? null),
                'status'          => !empty($schedule_datetime) ? 'scheduled' : 'published',
                'response'        => json_encode($responseData),
                
                'scheduled_at'     => $scheduledCarbon,  // Carbon object → Laravel saves as datetime

                                       
            ]);
        }
        //Delete banner after share
        if (file_exists(public_path('images/download/'.$banner['filename']))) {
            unlink(public_path('images/download/'.$banner['filename']));
        }

        return response()->json([
            'success' => true,
            'message' => !empty($schedule_datetime)
                ? 'Post scheduled successfully'
                : 'Posted to page successfully',
            'type'    => 'published',
            'data' => $responses
        ]);        
    }

    
    /**
     * SocialController
     * Function name : videoPostCreation()
     * Purpose       : Prepare and load the social media video post
     *                 creation page by fetching reel video details 
     *                 for facebook or Instagram ,
     *                 generating the video thumbnail URL, and retrieving
     *                 connected Facebook page information when required.
     * Params        : $shareon, $slug
     */
    public function videoPostCreation($shareon, $slug)
    {   
        //dd($shareon);     
        $reel_video = Reelvideo::where('slug',$slug)->first();
        $banner_url = config('filesystems.s3_base_url') . '/public/uploads/reelvideo/' . $reel_video->cover_image;

        $data['video_slug'] = $reel_video->slug ?? '';
        //$data['shareOn'] = 'fb';
        $data['video_type']='reels';
        $data['shareOn'] = $shareon;
        $fbData = '';
        if($shareon == 'facebook'){         
            $fbData = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'fb')
            ->get();
        }
        $shareType= 'video';
        return view('frontend.social.post_creation', compact('banner_url', 'fbData', 'shareType'), $data);
    }

    
    /**
    * SocialController
    * Function name : createMarketingVideoPost()
    * Purpose       : Prepare and load the social media video post
    *                 creation page by fetching marketing video details,
    *                 generating the video thumbnail URL, and loading
    *                 connected social media account information for
    *                 publishing on Facebook, Instagram, or LinkedIn
    *                 based on the selected share platf
    * Params        : $shareon, $slug
    */  
    public function createMarketingVideoPost($shareon, $slug)
    {        
        $marketing_video = Marketingvideo::where('slug',$slug)->first();
        $banner_url = config('filesystems.s3_base_url') . '/public/uploads/marketingvideo/' . $marketing_video->cover_image;

        $data['video_slug'] = $marketing_video->slug ?? '';
        $data['shareOn'] = $shareon;
        $data['video_type']='marketing';
        $fbData = '';
        if($shareon == 'facebook'){ 
        $fbData = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'fb')
            ->get();
        }
        $shareType= 'video';
        return view('frontend.social.post_creation', compact('banner_url', 'fbData', 'shareType'), $data);
    }

   
    
    /**
     * SocialController
     * Function name : shareVideoOnFacebook()
     * Purpose       : Publish a branded reel or marketing video to one
     *                 or more connected Facebook Pages by generating the
     *                 video caption, applying branding to the video,
     *                 uploading the video to Facebook, and returning the
     *                 publishing status for each selected page.
     * Params        : Request $request
     */
    public function shareVideoOnFacebook(Request $request)
    {        
        $slug = $request->input('video_slug');
        $video_type = $request->input('video_type');
        if($video_type=='reels'){
            $detail = Reelvideo::where('slug',$slug)->first();
            $media_url = Storage::disk('s3')->url('public/uploads/reelvideo/video/'.$detail->video);
        }else{
            //For marketing video
            $detail = Marketingvideo::where('slug',$slug)->first();
            $media_url = Storage::disk('s3')->url('public/uploads/marketingvideo/video/'.$detail->video);
        
        }
        
        $video = $detail->video;
        $page_id        = $request->page_id;
        $text_message   = $request->text_message;
        $tags           = json_decode($request->tags, true);
        $description    = $request->description;
        $schedule_datetime = $request->schedule_datetime;

        // Build caption
        $tagString = '';
        if (!empty($tags)) {
            $tagString = ' #' . implode(' #', $tags);
        }
        $finalMessage = $text_message . "\n\n" . $description . "\n\n" . $tagString;

        $locationName = $request->location_name;
        if (!empty($locationName)) {
            $finalMessage .= "\n\n📍 " . $locationName;
        }

        // Get social accounts
        $socialAccounts = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'fb')
            ->when(!empty($page_id), function ($query) use ($page_id) {
                if (is_array($page_id)) {
                    $query->whereIn('page_id', $page_id);
                } else {
                    $query->where('page_id', $page_id);
                }
            })
            ->get();

        if ($socialAccounts->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook not connected',
                'type'    => 'not_connected',
            ]);
        }

        // Parse & validate schedule_datetime 
        $scheduledCarbon = null;
        if (!empty($schedule_datetime)) {
            preg_match(
                '/(\d{1,2})\s*-\s*(\d{1,2})\s*-\s*(\d{4})\s+(\d{1,2})\s*-\s*(\d{1,2})\s*(AM|PM)/i',
                $schedule_datetime,
                $matches
            );

            if (empty($matches)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid schedule datetime format',
                    'type'    => 'schedule'
                ]);
            }

            $hour = (int) $matches[4];
            $ampm = strtoupper($matches[6]);

            if ($ampm === 'AM' && $hour === 12) $hour = 0;
            elseif ($ampm === 'PM' && $hour !== 12) $hour += 12;

            $formatted = sprintf(
                '%s-%s-%s %s:%s:00',
                $matches[3],
                str_pad($matches[2], 2, '0', STR_PAD_LEFT),
                str_pad($matches[1], 2, '0', STR_PAD_LEFT),
                str_pad($hour,       2, '0', STR_PAD_LEFT),
                str_pad($matches[5], 2, '0', STR_PAD_LEFT)
            );

            $scheduledCarbon = \Carbon\Carbon::createFromFormat(
                'Y-m-d H:i:s',
                $formatted,
                'Asia/Kolkata'
            );

            if ($scheduledCarbon->timestamp < now('Asia/Kolkata')->addMinutes(10)->timestamp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheduled time must be at least 10 minutes in the future',
                    'type'    => 'schedule'
                ]);
            }

            if ($scheduledCarbon->timestamp > now('Asia/Kolkata')->addDays(30)->timestamp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scheduled time cannot be more than 30 days in the future',
                    'type'    => 'schedule'
                ]);
            }
        }

        // Get video URL from request or use default
        /*$video_url = $request->video_url 
            ?? 'https://masterstroke.s3.ap-south-1.amazonaws.com/public/uploads/reelvideo/video/1769163761.mp4';*/

        $brandingVideoUrl = SocialMediaHelper::videoBrandingUrl($slug, $video, $video_type);

        // Extract filename from URL
        $video_filename   = basename($brandingVideoUrl); 
       
       //dd($brandingVideoUrl);

        $responses = [];

        foreach ($socialAccounts as $account) {
            $pageId    = $account->page_id;
            $pageToken = $account->page_token;

            try {
                // Build payload with optional scheduling
                $payload = [
                    'file_url'     => $brandingVideoUrl,
                    'description'  => $finalMessage,
                    'access_token' => $pageToken,
                ];

                if ($scheduledCarbon) {
                    $payload['published']              = false;
                    $payload['scheduled_publish_time'] = $scheduledCarbon->timestamp;
                }

                $response = Http::post(
                    "https://graph.facebook.com/v19.0/$pageId/videos",
                    $payload
                );
                
                 // Direct video post using file_url (no upload session needed)
                /*$response = Http::post(
                    "https://graph.facebook.com/v19.0/$pageId/videos",
                    [
                        //'file_url'     => $video_url,      // S3 public URL
                        'file_url'  => $brandingVideoUrl,
                        'description'  => $finalMessage,
                        'access_token' => $pageToken,
                    ]
                );*/
                /* Save publish history */
                SocialPostHistory::create([
                    'user_id'         => Auth::id(),
                    'provider'        => 'fb',
                    'page_id'         => $pageId,
                    'page_name'       => $account->page_name ?? null,
                    'banner_url'      => $media_url ?? '',
                    'message'         => $finalMessage,
                    'facebook_post_id'=> $responseData['post_id'] ?? ($responseData['id'] ?? null),
                    'status'          => !empty($schedule_datetime) ? 'scheduled' : 'published',
                    'response'        => json_encode($responseData),
                    
                    'scheduled_at'     => $scheduledCarbon,  // Carbon object → Laravel saves as datetime
                                      
                ]);

                $responseData = $response->json();

                $responses[] = [
                    'page_id' => $pageId,
                    'success' => !isset($responseData['error']),
                    'response'=> $responseData,
                ];         

            } catch (\Exception $e) {
                $responses[] = [
                    'page_id' => $pageId,
                    'success' => false,
                    'error'   => $e->getMessage(),
                ];
            }
        }

        // =============================================
        // Delete local file after all posts done
        // =============================================
         $video_local_path = public_path('uploads/reelvideo/' . $video_filename);

        if (file_exists($video_local_path)) {
            unlink($video_local_path);
            \Log::info('Deleted local video: ' . $video_local_path);
        }

        return response()->json([
            'success' => true,
            //'message' => 'Posted to page successfully',
            'message' => !empty($schedule_datetime)
                ? 'Post scheduled successfully'
                : 'Posted to page successfully',
            'type'    => 'published',
            'data'    => $responses,
        ]);
    }



    //===Instagram ============
    public function redirectToInstagramold()
    {
        $clientId    = env('INSTAGRAM_CLIENT_ID');
        $redirectUri = urlencode(env('INSTAGRAM_REDIRECT_URI'));

        // Instagram Business uses Facebook OAuth
        $scopes = urlencode(implode(',', [
            'instagram_basic',
            'instagram_content_publish',
            'instagram_manage_insights',
            'pages_show_list',
            'pages_read_engagement',
        ]));

        $url = "https://www.facebook.com/v19.0/dialog/oauth"
             . "?client_id={$clientId}"
             . "&redirect_uri={$redirectUri}"
             . "&scope={$scopes}"
             . "&response_type=code"
             . "&state=" . csrf_token();

        return redirect($url);
    }

  
    // =============================================
    // STEP 1: Redirect to Facebook/Instagram OAuth
    // ============================================

    public function redirectToInstagram()
    {
        $clientId = env('INSTAGRAM_CLIENT_ID');
        $redirectUri = urlencode(env('INSTAGRAM_REDIRECT_URI'));
        
        /*$url = "https://www.instagram.com/oauth/authorize"
            . "?force_reauth=true"
            . "&client_id={$clientId}"
            . "&redirect_uri={$redirectUri}"
            . "&response_type=code"
            . "&scope=instagram_business_basic,instagram_business_content_publish";*/


        $url = "https://www.instagram.com/oauth/authorize"
        . "?client_id={$clientId}"
        . "&redirect_uri={$redirectUri}"
        . "&response_type=code"
        . "&scope=instagram_business_basic,instagram_business_content_publish";
        
        return redirect($url);
    }

    // =============================================
    // STEP 2: Handle Callback & Save Token
    // =============================================
    public function handleInstagramCallback(Request $request)
    {
        $code = $request->get('code');
        //dd($code);

        if (!$code) {
            return redirect()->route('social.setting')
                ->with('error', 'Instagram connection failed.');
        }

        // Step 1: Get short-lived token
        $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
            'client_id' => env('INSTAGRAM_CLIENT_ID'),
            'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
            'grant_type' => 'authorization_code',
            'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
            'code' => $code,
        ])->json();

        if (!isset($response['access_token'])) {
            return redirect()->route('social.setting')
                ->with('error', 'Failed to get Instagram access token.');
        }

        $shortLivedToken = $response['access_token'];
        $igUserId = $response['user_id'];
        //dd($shortLivedToken);

        // Step 2: Exchange for long-lived token (valid 60 days)
        $longLivedResponse = Http::get('https://graph.instagram.com/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
            'access_token' => $shortLivedToken,
        ])->json();



        if (!isset($longLivedResponse['access_token'])) {
            return redirect()->route('social.setting')
                ->with('error', 'Failed to get long-lived token.');
        }

        $accessToken = $longLivedResponse['access_token'];
        $expiresIn = $longLivedResponse['expires_in']; // seconds (about 5184000 = 60 days)
        $expiresAt = now()->addSeconds($expiresIn);

        // Step 3: Get Instagram user details
        $igUser = Http::get("https://graph.instagram.com/v21.0/{$igUserId}", [
            'fields' => 'id,username,account_type',
            'access_token' => $accessToken
        ])->json();

        // Step 4: Save to database with expiry
        UserSocialAccount::updateOrCreate(
            [
                'user_id' => Auth::id(),                
                'provider' => 'insta',
                'page_id' => $igUserId,
            ],
            [
                'provider_user_id' => $igUserId,
                'access_token' => $accessToken,
                'page_name' => $igUser['username'] ?? 'Instagram Account',
                'page_token' => $accessToken,
                'instagram_account_id' => $igUserId,
                //'instagram_username' => $igUser['username'] ?? null,
                'username' => $igUser['username'] ?? null,
                'token_expires_at' => $expiresAt,
            ]
        );

        return redirect()->route('social.setting')
            ->with('message', 'Instagram connected successfully.');
    }


    /**
     * SocialController
     * Function name : postToInstagram()
     * Purpose       : Publish a branded image post to Instagram by
     *                 creating a media container, publishing the image
     *                 with the provided caption and hashtags, storing
     *                 the publish history, and removing the temporary
     *                 branded banner after successful posting.
     * Params        : Request $request
    */
    public function postToInstagram(Request $request)
    {         
        //$imageUrl= 'https://masterstrokeonline.s3.ap-south-1.amazonaws.com/public/uploads/premiumbanner/1768971026.jpg';
     
        $imageUrl = $request->banner_url;
        $banner = FrontHelper::bannerBranding($imageUrl);
        $banner_url = $banner['url'];
        //dd($banner_url);

        $text_message = $request->text_message;
        $tags = json_decode($request->tags, true);
        $description = $request->description;

         // Convert tags to hashtags
        $tagString = '';
        if (!empty($tags)) {
            $tagString = ' #' . implode(' #', $tags);
        }

        // Final message (caption + description + tags)
        $finalMessage = $text_message . "\n\n" . $description . "\n\n" . $tagString;     

        $account = UserSocialAccount::where('user_id', Auth::id())
                ->where('provider', 'insta')->where('token_expires_at', '>', now())
                ->first();

        if (!$account) {
            return ['error' => 'No Instagram account connected'];
        }

        // Debug — check what values are stored       

        $igId = $account->instagram_account_id;
        $accessToken = $account->access_token;
        //$igId = $account->provider_user_id; //need to remove        

        // Step 1: Create media container
        $container = Http::post("https://graph.instagram.com/v21.0/{$igId}/media", [
            //'image_url' => $imageUrl,
            'image_url' => $banner_url,            
            'caption' => $finalMessage,
            'access_token' => $accessToken
        ])->json();

        if (!isset($container['id'])) {
            return ['error' => 'Failed to create media container', 'details' => $container];
        }

        // Step 2: Wait for processing
        sleep(3);

        // Step 3: Publish
        $publish = Http::post("https://graph.instagram.com/v21.0/{$igId}/media_publish", [
            'creation_id' => $container['id'],
            'access_token' => $accessToken
        ])->json();


         /* Save publish history */
            SocialPostHistory::create([
                'user_id'         => Auth::id(),
                'provider'        => 'instagram',
                'page_id'         => $account->page_id ?? null,
                'page_name'       => $account->page_name ?? null,
                //'banner_url'      => $banner_url,
                'banner_url'      => $imageUrl,                
                'message'         => $finalMessage,
                //'post_id'=> $publish['post_id'] ?? ($publish['id'] ?? null),
                'status'          => 'published',
                'response'        => json_encode($publish),
                
                                       
            ]);
        //}
        //Delete banner after share
        if (file_exists(public_path('images/download/'.$banner['filename']))) {
            unlink(public_path('images/download/'.$banner['filename']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Posted to page successfully',
            'type'    => 'published',
            'data' => $publish
        ]); 

       
    }

    //=====For Testing==========
    public function postVideoToInstagram(Request $request)
    {        
        $slug = $request->input('video_slug');
        $video_type = $request->input('video_type');
        if($video_type=='reels'){
            $detail = Reelvideo::where('slug',$slug)->first();
            $media_url = Storage::disk('s3')->url('public/uploads/reelvideo/video/'.$detail->video);
        }else{
            //For marketing video
            $detail = Marketingvideo::where('slug',$slug)->first();
            $media_url = Storage::disk('s3')->url('public/uploads/marketingvideo/video/'.$detail->video);
        }
        
        $video = $detail->video;
        //$page_id        = $request->page_id;
        $text_message   = $request->text_message;
        $tags           = json_decode($request->tags, true);
        $description    = $request->description;

        // Build caption
        $tagString = '';
        if (!empty($tags)) {
            $tagString = ' #' . implode(' #', $tags);
        }
        $finalMessage = $text_message . "\n\n" . $description . "\n\n" . $tagString;

        $locationName = $request->location_name;
        if (!empty($locationName)) {
            $finalMessage .= "\n\n📍 " . $locationName;
        }

        // Get social accounts
        $socialAccounts = UserSocialAccount::where('user_id', Auth::id())
                ->where('provider', 'insta')->where('token_expires_at', '>', now())
                ->first();

        if (!$socialAccounts) {
            return response()->json([
                'success' => false,
                'message' => 'Instagram not connected',
                'type'    => 'not_connected',
            ]);
        }

        // Get video URL from request or use default
        /*$videoUrl = $request->video_url 
            ?? 'https://masterstroke.s3.ap-south-1.amazonaws.com/public/uploads/reelvideo/video/1769163761.mp4';*/

        $brandingVideoUrl = SocialMediaHelper::videoBrandingUrl($slug, $video, $video_type);

        // Extract filename from URL
        $video_filename   = basename($brandingVideoUrl); 
       
       //dd($brandingVideoUrl);

        $responses = [];

         
        $igId        = $socialAccounts->instagram_account_id;
        $accessToken = $socialAccounts->access_token;

        // ------------------------------------------------------------------
        // STEP 1: Create media container (REELS type for video)
        // ------------------------------------------------------------------
        $containerPayload = [
            'media_type'  => 'REELS',       // Use REELS for video posts on Instagram
            //'video_url'   => $videoUrl,
            'video_url'   => $brandingVideoUrl,            
            'caption'     => $finalMessage,
            'access_token'=> $accessToken,
            'share_to_feed' => true,        // Show in main feed, not just Reels tab
        ];
        
        $container = Http::post(
            "https://graph.instagram.com/v21.0/{$igId}/media",
            $containerPayload
        )->json();

        if (!isset($container['id'])) {
            return response()->json([
                'error'   => 'Failed to create video container',
                'details' => $container,
            ], 422);
        }

        $containerId = $container['id'];

        // ------------------------------------------------------------------
        // STEP 2: Poll until video processing is FINISHED (not just sleep)
        // ------------------------------------------------------------------
        $maxAttempts = 15;   // ~75 seconds max wait
        $attempt     = 0;
        $status      = '';

        while ($attempt < $maxAttempts) {
            sleep(5);

            $statusResponse = Http::get(
                "https://graph.instagram.com/v21.0/{$containerId}",
                [
                    'fields'       => 'status_code,status',
                    'access_token' => $accessToken,
                ]
            )->json();

            $status = $statusResponse['status_code'] ?? '';

            if ($status === 'FINISHED') {
                break;
            }

            if ($status === 'ERROR') {
                return response()->json([
                    'error'   => 'Video processing failed on Instagram side',
                    'details' => $statusResponse,
                ], 422);
            }

            $attempt++;
        }

        if ($status !== 'FINISHED') {
            return response()->json([
                'error' => 'Video processing timed out. Try again later.',
            ], 408);
        }

        // ------------------------------------------------------------------
        // STEP 3: Publish
        // ------------------------------------------------------------------
        $publish = Http::post(
            "https://graph.instagram.com/v21.0/{$igId}/media_publish",
            [
                'creation_id' => $containerId,
                'access_token'=> $accessToken,
            ]
        )->json();

        // ------------------------------------------------------------------
        // Save publish history (mirrors your image method)
        // ------------------------------------------------------------------
        SocialPostHistory::create([
            'user_id'    => Auth::id(),
            'provider'   => 'instagram',
            'page_id'    => $socialAccounts->page_id   ?? null,
            'page_name'  => $socialAccounts->page_name ?? null,
            'banner_url' => $media_url ?? '', // To do need to change table column name 
            'message'    => $finalMessage,
            'status'     => isset($publish['id']) ? 'published' : 'failed',
            'response'   => json_encode($publish),
        ]);

        if (!isset($publish['id'])) {
            return response()->json([
                'error'   => 'Publish step failed',
                'details' => $publish,
            ], 422);
        }
        
        // =============================================
        // Delete local file after all posts done
        // =============================================
         $video_local_path = public_path('uploads/reelvideo/' . $video_filename);

        if (file_exists($video_local_path)) {
            unlink($video_local_path);
            \Log::info('Deleted local video: ' . $video_local_path);
        }

        return response()->json([
            'success' => true,
            'message' => 'Posted to page successfully',
            'type'    => 'published',
            'data'    => $responses,
        ]);
    }
 
    //Need to remove
    public function postVideoToInstagramzz(Request $request)
    {
    
        /*$request->validate([
            'video_url'   => 'required|url',
            'description' => 'nullable|string',
            'text_message'=> 'nullable|string',
            'tags'        => 'nullable|string', // JSON encoded array
            'thumb_url'   => 'nullable|url',    // Optional custom thumbnail
        ]);*/

        //$videoUrl    = $request->video_url;
        $videoUrl = $request->video_url 
            ?? 'https://masterstroke.s3.ap-south-1.amazonaws.com/public/uploads/reelvideo/video/1769163761.mp4';

        $textMessage = $request->text_message;
        $tags        = json_decode($request->tags, true);
        $description = $request->description;
        $thumbUrl    = $request->thumb_url; // optional

        // Build caption (same pattern as your image method)
        $tagString = '';
        if (!empty($tags)) {
            $tagString = ' #' . implode(' #', $tags);
        }
        $finalMessage = $textMessage . "\n\n" . $description . "\n\n" . $tagString;

        // Fetch connected Instagram account
        $account = UserSocialAccount::where('user_id', Auth::id())
            ->where('provider', 'insta')
            ->where('token_expires_at', '>', now())
            ->first();

        if (!$account) {
            return response()->json(['error' => 'No Instagram account connected'], 401);
        }

        $igId        = $account->instagram_account_id;
        $accessToken = $account->access_token;

        // ------------------------------------------------------------------
        // STEP 1: Create media container (REELS type for video)
        // ------------------------------------------------------------------
        $containerPayload = [
            'media_type'  => 'REELS',       // Use REELS for video posts on Instagram
            'video_url'   => $videoUrl,
            'caption'     => $finalMessage,
            'access_token'=> $accessToken,
            'share_to_feed' => true,        // Show in main feed, not just Reels tab
        ];

        if ($thumbUrl) {
            $containerPayload['thumb_offset'] = 0; // or use cover_url if supported
            // Note: Instagram Graph API uses thumb_offset (ms) not a custom image
            // For custom thumbnail image, use: 'cover_url' => $thumbUrl (if your API version supports it)
        }

        $container = Http::post(
            "https://graph.instagram.com/v21.0/{$igId}/media",
            $containerPayload
        )->json();

        if (!isset($container['id'])) {
            return response()->json([
                'error'   => 'Failed to create video container',
                'details' => $container,
            ], 422);
        }

        $containerId = $container['id'];

        // ------------------------------------------------------------------
        // STEP 2: Poll until video processing is FINISHED (not just sleep)
        // ------------------------------------------------------------------
        $maxAttempts = 15;   // ~75 seconds max wait
        $attempt     = 0;
        $status      = '';

        while ($attempt < $maxAttempts) {
            sleep(5);

            $statusResponse = Http::get(
                "https://graph.instagram.com/v21.0/{$containerId}",
                [
                    'fields'       => 'status_code,status',
                    'access_token' => $accessToken,
                ]
            )->json();

            $status = $statusResponse['status_code'] ?? '';

            if ($status === 'FINISHED') {
                break;
            }

            if ($status === 'ERROR') {
                return response()->json([
                    'error'   => 'Video processing failed on Instagram side',
                    'details' => $statusResponse,
                ], 422);
            }

            $attempt++;
        }

        if ($status !== 'FINISHED') {
            return response()->json([
                'error' => 'Video processing timed out. Try again later.',
            ], 408);
        }

        // ------------------------------------------------------------------
        // STEP 3: Publish
        // ------------------------------------------------------------------
        $publish = Http::post(
            "https://graph.instagram.com/v21.0/{$igId}/media_publish",
            [
                'creation_id' => $containerId,
                'access_token'=> $accessToken,
            ]
        )->json();

        // ------------------------------------------------------------------
        // Save publish history (mirrors your image method)
        // ------------------------------------------------------------------
        SocialPostHistory::create([
            'user_id'    => Auth::id(),
            'provider'   => 'instagram',
            'page_id'    => $account->page_id   ?? null,
            'page_name'  => $account->page_name ?? null,
            'banner_url' => $videoUrl,
            'message'    => $finalMessage,
            'status'     => isset($publish['id']) ? 'published' : 'failed',
            'response'   => json_encode($publish),
        ]);

        if (!isset($publish['id'])) {
            return response()->json([
                'error'   => 'Publish step failed',
                'details' => $publish,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Video posted to Instagram successfully',
            'type'    => 'published',
            'data'    => $publish,
        ]);
    }
    
    /**
     * SocialController
     * Function name : disconnectInstagram()
     * Purpose       : Disconnect the user's Instagram account by
     *                 revoking Instagram permissions, removing stored
     *                 account details from the database, and clearing
     *                 the social media connection.
     * Params        : None
     */
    public function disconnectInstagram()
    {
        try {
            $accounts = UserSocialAccount::where('user_id', Auth::id())
                ->where('provider', 'insta') // ✅ Fix: 'insta' → 'instagram'
                ->get();

            if ($accounts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Instagram account connected.'
                ]);
            }

            // Revoke token and delete each account
            foreach ($accounts as $account) {
                // Revoke token from Instagram
                try {
                    Http::delete("https://graph.instagram.com/v21.0/{$account->provider_user_id}/permissions", [
                        'access_token' => $account->access_token,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Token revocation failed: ' . $e->getMessage());
                }

                // Delete from database
                $account->delete(); // ✅ Delete individual model not collection
            }

            return redirect()->route('social.setting')
                ->with('message', 'Instagram disconnected successfully.');

        } catch (\Exception $e) {
            \Log::error('Instagram disconnect failed: ' . $e->getMessage());
            return redirect()->route('social.setting')
                ->with('error', 'Failed to disconnect Instagram.');
        }
    }
    
}
