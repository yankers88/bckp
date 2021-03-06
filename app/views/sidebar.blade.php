<div class="col-250">
    <div class="hidden-tab" id="sidebar">
        <div id="fb-root"></div>


        <!-- =========================
            RIGHT SIDEBAR 
            ============================== -->
        <aside class="right-sidebar">
            <div class="widget widget-meme">
                <a href="#" class="btn btn-meme btn-block showregisterbox">Submit Your Content</a>
            </div>
            @if(Session::has('auth') && TagSuggests::where('owner','=',Session::get('auth')['username'])->count())
            <div class="widget widget-hashtag">
                <center><a href="/post/suggestions">Post Suggestions ({{TagSuggests::where('owner','=',Session::get('auth')['username'])->count()}})</a></center>
            </div>
            @endif
            @if(Session::has('auth') && Follow::where('user_id','=',Session::get('auth')['id'])->where('subscribed','=','no')->count())
            <div class="widget widget-hashtag">
                <center><a href="/post/subscription_summary">Subscription Summary ({{Follow::where('user_id','=',Session::get('auth')['id'])->where('subscribed','=','no')->count()}})</a></center>
            </div>
            @endif
            @if(Session::has('auth') && Follow::where('follower_id','=',Session::get('auth')['id'])->where('subscribed','=','yes')->count())
            <div class="widget widget-hashtag">
                <?php
                $following_names = Follow::where('follower_id', '=', Session::get('auth')['id'])->where('followers.subscribed', '=', 'yes')->join('users', 'users.id', '=', 'followers.user_id')->select('username')->get();

                for ($i = 0; $i < count($following_names); $i++) {
                    $following_name[$i]['username'] = $following_names[$i]['username'];
                    ?>
                                   <!-- <center><a href="/post/subscription_summary">Posts Summary ({{Post::where('username','=',$following_name[$i]['username'])->count()}})</a></center>-->
                <?php } ?>
            </div>
            @endif
            <div class="widget widget-hashtag">
                <h3>Trending Hashtag</h3>
                <div class="wrap">
                    <ul>
                        <?php
                        $report_post_tags = Tags::join('report', 'report.target', '=', 'hashtags.p_id')
                                        ->where('report.reported_by', Session::get('auth')['username'])
                                        ->where('report.type', '=', 'post')
                                        ->select('hashtags.tags')->get();
                        $repor_tags = json_decode(json_encode($report_post_tags), true);
                        $block_post_tags = DB::table('hashtags')
                                        ->join('posts', 'posts.p_id', '=', 'hashtags.p_id')
                                        ->leftjoin('block_users', 'block_users.blocked_username', '=', 'posts.username')
                                        ->where('block_users.blocked_by', Session::get('auth')['username'])
                                        ->select('hashtags.tags')->get();
                        $block_tags = json_decode(json_encode($block_post_tags), true);
                        $combined_array = array_merge($repor_tags, $block_tags);
                        if (!empty($combined_array)) {
                            $tags = Tags::orderBy('created_at', 'DESC')->groupBy('tags')
                                    ->where('language', '=', Session::get('language'))
                                    ->WhereNotIn('tags', array($combined_array))
                                    ->WhereNotIn('tags', function($q) {
                                        $q->select('hashtag')
                                        ->from('posts_hidden')
                                        ->where('username', '=', Session::get('auth')['username']);
                                    })
                                    ->skip(0)
                                    ->take(5)
                                    ->get();
                        } else {

                            $tags = Tags::orderBy('created_at', 'DESC')->groupBy('tags')
                                    ->where('language', '=', Session::get('language'))
                                    ->WhereNotIn('tags', function($q) {
                                        $q->select('hashtag')
                                        ->from('posts_hidden')
                                        ->where('username', '=', Session::get('auth')['username']);
                                    })
                                    ->skip(0)
                                    ->take(5)
                                    ->get();
                        }

                        foreach ($tags as $tag) {
                            ?>
                            <li><a href="/hashtag/{{$tag->tags}}">#{{$tag->tags}}</a></li>
                            <?php
                        }
                        ?>
                        @if(!count($tags))
                        <center>
                            <p>Not Enough Data</p>
                        </center>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="widget widget-popular">
                <h3>Popular Now</h3>
                <div class="wrap">

                    <div id="popularnow" class="carousel slide" data-ride="carousel">
                        <!-- Wrapper for slides -->
                        <div class="carousel-inner" role="listbox">
                            <?php
                            $blocked_user_names = DB::table('block_users')->get();
                            $block_array = json_decode(json_encode($blocked_user_names), true);
                            $report_posts = DB::table('posts')->join('report', 'report.target', '=', 'posts.p_id')
                                            ->where('report.reported_by', Session::get('auth')['username'])->select('posts.p_id')->get();
                            $repor_arr = json_decode(json_encode($report_posts), true);
                            $block_post = DB::table('posts')
                                            ->leftjoin('block_users', 'block_users.blocked_username', '=', 'posts.username')
                                            ->where('block_users.blocked_by', Session::get('auth')['username'])
                                            ->select('posts.p_id')->get();
                            $arr = json_decode(json_encode($block_post), true);
                            $combined_array = array_merge($repor_arr, $arr);
                            if (!empty($combined_array)) {
                                $posts = Post::orderBy('points', 'DESC')
                                        ->WhereNotIn('posts.p_id', array($combined_array))
                                        ->WhereNotIn('p_id', function($q) {
                                            $q->select('p_id')
                                            ->from('hashtags')
                                            ->WhereIn('tags', function($y) {
                                                $y->select('hashtag')
                                                ->from('posts_hidden')
                                                ->where('username', '=', Session::get('auth')['username']);
                                            });
                                        })
                                        ->where('language', Session::get('language'))
                                        ->orWhere('type', 'gif')
                                        ->where('type', 'photo')
                                        ->orderBy('created_at', 'DESC')
                                        ->skip(0)
                                        ->take(5)
                                        ->get();
                            } else {
                                $posts = Post::orderBy('points', 'DESC')
                                        ->WhereNotIn('p_id', function($q) {
                                            $q->select('p_id')
                                            ->from('hashtags')
                                            ->WhereIn('tags', function($y) {
                                                $y->select('hashtag')
                                                ->from('posts_hidden')
                                                ->where('username', '=', Session::get('auth')['username']);
                                            });
                                        })
                                        ->where('language', Session::get('language'))
                                        ->orWhere('type', 'gif')
                                        ->where('type', 'photo')
                                        ->orderBy('created_at', 'DESC')
                                        ->skip(0)
                                        ->take(5)
                                        ->get();
                            }
                            foreach ($posts as $post) {
                                ?>
                                <div class="popular-post item">
                                    @if($post->type === 'photo' || $post->type === 'gif')
                                    <h1><a target="_blank" href="/{{$code}}/g/{{$post->p_id}}">{{$post->title}}</a></h1>
                                    <div class="ref-image">
                                        <a target="_blank" href="/{{$code}}/g/{{$post->p_id}}"><img src="{{$post->uri}}" alt="" class="img-responsive"></a>
                                    </div>
                                    @elseif($post->type === 'video' && $post->source === 'vine')
                                        <!--<iframe src="{{$post->uri}}/embed/simple?audio=1" width="300" height="300" frameborder="0"></iframe><script src="https://platform.vine.co/static/scripts/embed.js"></script>-->
                                    @elseif($post->type === 'video' && $post->source === 'youtube')
                                        <!--<iframe width="300" height="300" src="{{$post->uri}}" frameborder="0" allowfullscreen></iframe>-->
                                    @endif

                                </div>
                                <?php
                            }
                            ?>
                        </div>

                        <!-- Controls -->
                        <a class="left carousel-control" href="#popularnow" role="button" data-slide="prev">
                            <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="right carousel-control" href="#popularnow" role="button" data-slide="next">
                            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="widget widget-meme">
                @if(!Session::has('auth'))
                <a href="#" class="btn btn-meme btn-block showregisterbox">Upload Your Image!</a>
                @else
                <a href="#" class="btn btn-meme btn-block postfromurl">Upload Your Image!</a>
                @endif
            </div>
            <!--        <div class="widget widget-language">
                        <h3>Select language</h3>
                        <ul>
            
            <?php
//                $languages = Language::where('enabled', '=', 1)->lists('name');
//
//                foreach ($languages as $k => $v) {
//                    if ($v === Session::get('language')) {
//                        echo '<li class="active"><a href="/set_language/' . $v . '">' . $v . '</a></li>';
//                    } else {
//                        echo '<li><a href="/set_language/' . $v . '">' . $v . '</a></li>';
//                    }
//                }
            ?>
                        </ul>
                        <a href="#" class="continue">More</a>
                    </div>-->
            <div class="widget widget-subscribe">
                <h3>Subscribe to Newsletter</h3>
                <div class="wrap">
                    <form class="newsletter-subscribe" action="/subscribe" method="post" id="newsletter">
                        <div class="input-group clearfix">
                            <div class="input-element">
                                <input type="text" placeholder="Email Address" name="email" class="input-control" required>
                            </div>
                            <div class="input-btn">
                                <button type="submit">Subscribe</button>
                            </div>
                        </div>

                        @if($errors->has('email'))
                        *{{$errors->first('email')}}
                        @endif
                    </form>
                </div>
            </div>
            <div class="widget widget-follow">
                <h3>Follow us on Social Media:</h3>
                <div class="wrap">
                    <ul>
                        <li><a href="#"><i class="fa fa-facebook-square"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter-square"></i></a></li>
                        <li><a href="#"><i class="fa fa-google-plus-square"></i></a></li>
                    </ul>
                </div>
            </div>
            <div class="widget page-link">
                <div class="wrap">
                    <ul>
                        <li><a href="#">About Us</i></a></li>
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Terms</a></li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
    <div class="hidden-tab" id="sticky_me">

    </div>
</div>
<!-- #sticky sidebar -->
