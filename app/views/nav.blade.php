<?php
if (Session::has('auth')) {
    $get_usernames = User::join('followers', 'followers.user_id', '=', 'users.id')
                    ->where('followers.follower_id', Session::get('auth')['id'])
                    ->select('username')->get();
    $get_data = json_decode(json_encode($get_usernames), true);
    $get_followers = User::join('followers', 'followers.follower_id', '=', 'users.id')
                    ->select('username')->get();
    $get_follow = json_decode(json_encode($get_followers), true);
    $get_data = json_decode(json_encode($get_usernames), true);
    for ($i = 0; $i < count($get_usernames); $i++) {
        $user_array = array();
        $user_array[$i]['username'] = $get_data[$i]['username'];
        $get_last_post[$i]['post_id'] = Post::where('username', $user_array[$i]['username'])->orderBy('id', 'DESC')->first()->id;
        $check_post = Post::where('username', $user_array[$i]['username'])->where('id', '>', $get_last_post[$i]['post_id'])->where('created_at', '>', new DateTime('today'))->get();
        $notification_count = Notification::where('action_by', $user_array[$i]['username'])->where('action_for', $get_follow[0]['username'])->count();
    }

    if (isset($notification_count) && $notification_count == 0 && $check_post) {
        $get_follow_data = Follow::where('follower_id', '=', Session::get('auth')['id'])
                        ->where('subscribed', '=', 'yes')->get();
        $follow_data = json_decode(json_encode($get_follow_data), true);

        if (!empty($get_follow_data)) {
            for ($i = 0; $i < count($get_follow_data); $i++) {
                $follow = array();
                $follow[$i]['user_id'] = $follow_data[$i]['user_id'];
                $follow[$i]['follower_id'] = $follow_data[$i]['follower_id'];
                /*
                  | Create postsuggestion Notification
                 */
                Notify::suggestPost($follow[$i]['user_id'], $follow[$i]['follower_id']);
            }
        }
    }
}
?>
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>                        
            </button>
            <a class="navbar-brand" href="{{URL::secure('/')}}">VH</a>
        </div>
        <div class="collapse navbar-collapse" id="myNavbar">
            <ul class="nav navbar-nav">
                <li><a href="{{URL::secure('/')}}/sort/hot">Hot</a></li>
                <li><a href="{{URL::secure('/')}}/sort/fresh">Warm</a></li>
                <li><a href="{{URL::secure('/')}}/sort/gif">Fresh</a></li>
                <li><a href="{{URL::secure('/')}}/sort/controversial">Channels</a></li>

                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">More <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <?php
                        $categories = MetaData::where('type', '=', 'details')->first()->categories;
                        $categories = explode(',', $categories);

                        foreach ($categories as $category) {
                            ?>
                            <li><a href="{{URL::secure('/')}}/c/{{str_replace(' ','',$category)}}">{{ucfirst(str_replace(' ','',$category))}}</a></li>
                            <?php
                        }
                        ?>
                    </ul>
                </li>
                <li><a class="showlanguages" href="#">Category</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <select id="language" tabindex="1">
                        <option>English</option>
                        <option>繁體中</option>
                        <option>簡體中文</option>
                        <option>日本語</option>
                        <option>Español</option>
                        <option>Português</option>
                        <option>Русский</option>
                        <option>Türkçe</option>
                    </select>
                </li>
<!--                <li><a href="#" class="search"><span class="flaticon-search"></span></a></li>-->
                @if(Session::has('auth'))
                <li class="dp-socket"><span class="profilepic"><img class="navbar-dp" src="{{Session::get('auth')['dp_uri']}}"> </span></li>
                <li class="dropdown">
                    <a class="dropdown-toggle dp-dropdown" data-toggle="dropdown" href="#"> Me <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="/s/{{Session::get('auth')['username']}}"><span class="glyphicon glyphicon-user"></span> My Profile</a></li>
                        <li><a href="{{URL::secure('/')}}/settings"><span class="glyphicon glyphicon-cog"></span> Settings</a></li>
                        <li><a href="{{URL::secure('/')}}/logout"><span class="glyphicon glyphicon-log-in"></span> Logout</a></li>
                    </ul>
                </li>

                <!--Notification Dropdown-->

                <li class="dropdown notifications read-notifications">
                    <?php
                    $notifications = Notification::orderBy('created_at', 'DESC')
                                    ->where('action_for', '=', Session::get('auth')['username'])
                                    ->where('status', '=', 1)->get();
                    ?>
                    <a class="dropdown-toggle dp-dropdown" data-toggle="dropdown" href="#"> 
                        <i class="flicon flaticon-notify notify"> 
                            @if(Notification::where('viewed','=',0)
                            ->where('action_for','=',Session::get('auth')['username'])->count())
                            {{Notification::where('viewed','=',0)->where('action_for','=',Session::get('auth')['username'])->count()}}
                            @endif
                        </i>
                    </a>
                    <ul class="dropdown-menu">
                        @if($notifications->count() == 0)
                        <p class="no-notifications">No New Notifications</p>
                        @endif
                        @if($notifications->count())
                        <div class="readall-notifications">
                            <a class="readall">Read all Notifications</a>
                        </div>
                        @endif
                        <?php
                        foreach ($notifications as $notification) {

                            $ago = date('Y-m-d H:i:s', strtotime($notification->created_at));
                            $UTC = new DateTimeZone("UTC");
                            $newTZ = new DateTimeZone(Session::get('timezone'));
                            $date = new DateTime($ago, $UTC);
                            $date->setTimezone($newTZ);
                            $notification->tstamp = TimeZoneController::getElapsedTime($date->format('Y-m-d H:i:s'));

                            if ($notification->action === "comment") {

                                $dp = User::where('username', '=', $notification->action_by)->first()->dp_uri;
                                ?>
                                <a href="/{{$code}}/g/{{$notification->target}}#comments" data-target="{{$notification->target}}" data-action="{{$notification->action}}" data-status="{{$notification->status}}" data-viewed="{{$notification->viewed}}" data-id="{{$notification->id}}" class="notify-action">
                                    <li>
                                        <div class="single">
                                            <div class="col-icon">
                                                <i class="flicon flaticon-comment"></i>
                                            </div>
                                            <div class="col-txt">
                                                <p class="msg">{{$notification->action_by}} commented on your post.</p>
                                                <p class="meta">{{$notification->tstamp}}</p>
                                            </div>
                                            <div class="col-avtr">
                                                <img class="notify" src="{{$dp}}" alt="RRnew2">
                                            </div>
                                        </div>
                                    </li>
                                </a>
                                <?php
                            }

                            if ($notification->action === "postlike") {
                                $dp = User::where('username', '=', $notification->action_by)->first()->dp_uri;
                                ?>
                                <a href="/{{$code}}/g/{{$notification->target}}" data-target="{{$notification->target}}" data-action="{{$notification->action}}" data-status="{{$notification->status}}" data-viewed="{{$notification->viewed}}" data-id="{{$notification->id}}" class="notify-action">
                                    <li>
                                        <div class="single">
                                            <div class="col-icon">
                                                <i class="flicon flaticon-thumbs-up-alt"></i>
                                            </div>
                                            <div class="col-txt">
                                                <p class="msg">{{$notification->action_by}} upvoted your post.</p>
                                                <p class="meta">{{$notification->tstamp}}</p>
                                            </div>
                                            <div class="col-avtr">
                                                <img class="notify" src="{{$dp}}" alt="RRnew2">
                                            </div>
                                        </div>
                                    </li>
                                </a>
                                <?php
                            }

                            if ($notification->action === "suggestedpost") {
                                $dp = User::where('username', '=', $notification->action_by)->first()->dp_uri;
                                $last_post = Post::where('username', $notification->action_by)->orderBy('id', 'DESC')->first()->id;
                                $get_post = Post::where('username', '=', $notification->action_by)
                                                ->where('id', '>=', $last_post)
                                                ->where('created_at', '>=', new DateTime('today'))->get();
                                $today = 'today';
                                ?>
                                <a href="/{{$code}}/g/{{$notification->target}}" data-target="{{$notification->target}}" data-action="{{$notification->action}}" data-status="{{$notification->status}}" data-viewed="{{$notification->viewed}}" data-id="{{$notification->id}}" class="notify-action">
                                    <li>
                                        <div class="single">
                                            <div class="col-icon">
                                                <i class="flicon flaticon-thumbs-up-alt"></i>
                                            </div>
                                            <div class="col-txt">
                                                @if(count($get_post) ==1)
                                                <p class="msg">{{$notification->action_by}} Posted {{count($get_post)}} post.</p>
                                                <p class="meta">{{$today}}</p>
                                                @else
                                                <p class="msg">{{$notification->action_by}} Posted {{count($get_post)}} posts.</p>
                                                <p class="meta">{{$today}}</p>
                                                @endif
                                            </div>
                                            <div class="col-avtr">
                                                <img class="notify" src="{{$dp}}" alt="RRnew2">
                                            </div>
                                        </div>
                                    </li>
                                </a>
                                <?php
                            }
                            if ($notification->action === "removeprofile") {
                                $dp = User::where('username', '=', $notification->action_for)->first()->dp_uri;

                                $url = User::where('username', '=', $notification->action_for)->first()->username;
                                ?>
                                <a href="" data-target="{{$notification->target}}" data-action="{{$notification->action}}" data-status="{{$notification->status}}" data-viewed="{{$notification->viewed}}" data-id="{{$notification->id}}" class="notify-action">
                                    <li>
                                        <div class="single">
                                            <div class="col-icon">
                                                <i class="flicon flaticon-comment"></i>
                                            </div>
                                            <div class="col-txt">
                                                <p class="msg">{{$notification->action_by}} removed your Profile pic because it is an inappropriate image.</p>
                                                <p class="meta">{{$notification->tstamp}}</p>
                                            </div>
                                            <div class="col-avtr">
                                                <img class="notify" src="{{$dp}}" alt="RRnew2">
                                            </div>
                                        </div>
                                    </li>
                                </a>
                                <?php
                            }
                            if ($notification->action === "commentlike") {
                                $dp = User::where('username', '=', $notification->action_by)->first()->dp_uri;
                                $url = Comment::where('id', '=', $notification->target)->first()->p_id;
                                ?>
                                <a href="/{{$code}}/g/{{$url}}#comments" data-target="{{$notification->target}}" data-action="{{$notification->action}}" data-status="{{$notification->status}}" data-viewed="{{$notification->viewed}}" data-id="{{$notification->id}}" class="notify-action">
                                    <li>
                                        <div class="single">
                                            <div class="col-icon">
                                                <i class="flicon flaticon-comment"></i>
                                            </div>
                                            <div class="col-txt">
                                                <p class="msg">{{$notification->action_by}} upvoted your comment.</p>
                                                <p class="meta">{{$notification->tstamp}}</p>
                                            </div>
                                            <div class="col-avtr">
                                                <img class="notify" src="{{$dp}}" alt="RRnew2">
                                            </div>
                                        </div>
                                    </li>
                                </a>
                                <?php
                            }

                            if ($notification->action === 'commentreply') {
                                $dp = User::where('username', '=', $notification->action_by)->first()->dp_uri;
                                $url = Comment::where('id', '=', $notification->target)->first()->p_id;
                                ?>
                                <a href="/{{$code}}/g/{{$url}}#comments" data-target="{{$notification->target}}" data-action="{{$notification->action}}" data-status="{{$notification->status}}" data-viewed="{{$notification->viewed}}" data-id="{{$notification->id}}" class="notify-action">
                                    <li>
                                        <div class="single">
                                            <div class="col-icon">
                                                <i class="flicon flaticon-comment"></i>
                                            </div>
                                            <div class="col-txt">
                                                <p class="msg">{{$notification->action_by}} replied to your comment.</p>
                                                <p class="meta">{{$notification->tstamp}}</p>
                                            </div>
                                            <div class="col-avtr">
                                                <img class="notify" src="{{$dp}}" alt="RRnew2">
                                            </div>
                                        </div>
                                    </li>
                                </a>
                                <?php
                            }

                            if ($notification->action === 'replylike') {
                                $dp = User::where('username', '=', $notification->action_by)->first()->dp_uri;
                                $c_id = Reply::where('id', '=', $notification->target)->first()->comment_id;
                                $url = Comment::where('id', '=', $c_id)->first()->p_id;
                                ?>
                                <a href="/{{$code}}/g/{{$url}}#comments" data-target="{{$notification->target}}" data-action="{{$notification->action}}" data-status="{{$notification->status}}" data-viewed="{{$notification->viewed}}" data-id="{{$notification->id}}" class="notify-action">
                                    <li>
                                        <div class="single">
                                            <div class="col-icon">
                                                <i class="flicon flaticon-comment"></i>
                                            </div>
                                            <div class="col-txt">
                                                <p class="msg">{{$notification->action_by}} upvoted your reply.</p>
                                                <p class="meta">{{$notification->tstamp}}</p>
                                            </div>
                                            <div class="col-avtr">
                                                <img class="notify" src="{{$dp}}" alt="RRnew2">
                                            </div>
                                        </div>
                                    </li>
                                </a>
                                <?php
                            }
                        }// foreach
                        ?>
                    </ul>
                </li>
                <li class="dropdown ">
                    <a class="dropdown-toggle dp-dropdown" data-toggle="dropdown" href="#">  Upload <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        @if(Session::get('auth')['active'] == 1)
                        <li><a href="#" class="postfromurl">Add From URL</a></li>
                        <li><a href="#" class="postupload">Upload Image</a></li>
                        <li><a href="#" class="vineupload">Vine Videos Upload</a></li>
                        <li><a href="#" class="youtubeupload">Youtube Videos Upload </a></li>
                        @else
                        <li><a href="#" class="verifyemail">Verify Email</a></li>
                        @endif
                    </ul>
                </li>
                @else
                <li><a href="#" class="showregisterbox">Sign Up</a></li>
                <li><a href="#" class="showloginbox">Login</a></li>
                @endif
            </ul>
        </div>
    </div>
</nav>
