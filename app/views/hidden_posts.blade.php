@extends('index')

@section('title')
    @section('title')
        <title>{{MetaData::where('type','=','details')->first()->title}} @if($category) {{strtoupper($category)}} @endif</title>
    @stop
@stop

@section('posts')
<div class="post-container">
    <div class="row">
        <div class="col-615">
            <!-- =========================
                MAIN BODY 
                ============================== -->
            @if($posts->count())
            @if($posts->getLastPage() > 1)
                <main class="main-body content-to-load">
            @else
                <main class="main-body">
            @endif
            
            
                @foreach($posts as $post)
                
                <div class="post">
                    
                    <div class="header">
                        <div class="tags">
                            @foreach($post->tags as $tag)
                            <ul class="hashtag fl-right">
                                <li><a href="/hashtag/{{$tag}}">#{{$tag}}</a></li>
                            </ul>
                            @endforeach
                        </div>
                        <div class="title">
                            <h1><a  href="/{{$code}}/g/{{$post->p_id}}"> {{$post->title}}</a> </h1>
                        </div>
                        
                        @if(Session::has('auth'))
                        <div class="hiddenfor ">
                            <div class="reason">
                                Hidden Tag : 
                                
                                @foreach($post->tags as $tag)
                                    <?php
                                        $check = Hide::where('hashtag','=',$tag)->count();
                                    ?>
                                    @if($check)
                                        <a target="_blank" href="/hashtag/{{$tag}}">{{$tag}}</a>
                                    @endif
                                @endforeach
                            </div>
                            
                            <!--<div class="unhide-actions">
                                <button class="follow-content fl-right" data-tag="" data-pid="{{$post->p_id}}">Unhide Tag</button><br>
                            </div>-->
                        </div>
                        @endif
                        
                        @if(Session::has('auth'))
                            <div class="hidecontent">
                                <li class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="flicon flaticon-caret"></i> </a>
                                    <ul class="dropdown-menu dropdown-menu-right">
                                        <li><a class="follow-content" data-pid="{{$post->p_id}}" data-tag="" >Unhide Tag</a></li>
                                    </ul>
                                </li>
                            </div>
                        @endif
                        
                    </div>
                    <div class="imageholder" 
                            @if($post->type === 'photo' || $post->type === 'gif') 
                                @if(getimagesize(public_path().$post->uri)[1] > 800)
                                    style="height:500px; position:relative; overflow:hidden; display:block;" 
                                @endif
                            @endif
                    >
                        @if($post->type === 'photo' || $post->type === 'gif')
                            @if(getimagesize(public_path().$post->uri)[1] > 800)
                                <div class="fullimage"><a href="/{{$code}}/g/{{$post->p_id}}">View Full Post</a></div>
                            @endif
                            <a target="_blank" href="/{{$code}}/g/{{$post->p_id}}"><img src="{{URL::secure('/').$post->uri}}" alt="" class="img-responsive"></a>
                        @elseif($post->type === 'video' && $post->source === 'vine')
                            <iframe src="{{$post->uri}}/embed/simple" width="600" height="600" frameborder="0"></iframe><script src="https://platform.vine.co/static/scripts/embed.js"></script>
                        @elseif($post->type === 'video' && $post->source === 'youtube')
                            <iframe width="585" height="315" src="{{$post->uri}}" frameborder="0" allowfullscreen></iframe>
                        @endif
                        
                    </div>
                    
                    
                    <div class="social-activity">
                        <div class="share-post">
                            <ul>
                                <li>
                                    <a class="share" href="http://www.facebook.com/sharer.php?u={{URL::secure('/').'/'.$code.'/g/'.$post->p_id}}&t={{$post->title}}&t={{$post->title}}">
                                        <i class="flicon flaticon-facebook">Facebook</i>
                                    </a>
                                </li>
                                <li>
                                    <a class="share"  href="https://twitter.com/intent/tweet?via=9gag&text={{$post->title}}&source=tweetbutton&original_referer={{URL::secure('/')}}&url={{URL::secure('/').'/'.$code.'/g/'.$post->p_id}}&t={{$post->title}}&t={{$post->title}}">
                                        <i class="flicon flaticon-twitter">Twitter</i>
                                    </a>
                                </li>
                                <li><a href=""><i class="flicon flaticon-share"></i></a></li>
                            </ul>
                        </div>
                        <div class="votes">
                            <ul class="viewer-opinion">
                                @if(!Session::has('auth'))
                                <li><span ><i class="flicon flaticon-thumbs-up-alt showregisterbox"></i></span></li>
                                <li><span ><i class="flicon flaticon-thumbs-down-alt showregisterbox"></i></span></li>
                                @else
                                @if($post->up)
                                <li>
                                    <span >
                                    	<i data-pid="{{$post->p_id}}" data-username="{{$post->username}}" class="flicon flaticon-thumbs-up-alt upvote checked unvote"></i>
                                    </span>
                                </li>
                                
                                @else
                                <li>
                                	<span ><i data-pid="{{$post->p_id}}" data-username="{{$post->username}}" class="flicon flaticon-thumbs-up-alt upvote up-vote"></i>
                                	</span>
                                </li>
                                @endif
                                @if($post->down)
                                <li>
                                	<span >
                                		<i data-pid="{{$post->p_id}}" data-username="{{$post->username}}" class="flicon flaticon-thumbs-down-alt checked downvote unvotedown"></i>
                                	</span>
                                </li>
                                @else
                                <li>
                                	<span >
                                		<i data-pid="{{$post->p_id}}" data-username="{{$post->username}}" class="flicon flaticon-thumbs-down-alt downvote votedown"></i>
                                	</span>
                                </li>
                                @endif
                                @endif
                                <li><i class="flicon flaticon-fire"></i> <span class="totalpoints">{{$post->points}}</span> Points</li>
                                <li><a href="/{{$code}}/g/{{$post->p_id}}#comments"><i class="flicon flaticon-comment"></i>{{$post->c_count}}</a></li>
                            </ul>
                        </div>
                    </div>
                    <!-- end of .social-activity -->
                    
                    
                    <!-- SINGLE COMMENT VIEW -->
                    @if($post->first_comment)
                    <div class="comment-section clearfix">
                        <ul>
                            <li>
                                <div class="profilepic">
                                    <img src="{{$post->first_comment->comment_user_dp}}" alt="lp" class="thumb">
                                </div>
                                
                                
                                <div class="comments">
                                    <div class="blockquote">
                                        <header class="meta">
                                            
                                            <a target="_blank" href="/s/{{$post->first_comment->username}}">
                                                {{$post->first_comment->username}}
                                            </a>
                                            <span>{{$post->first_comment->tstamp}}</span>
                                        
                                        </header>
                                        <div class="comment-body">
                                            <p class="comment">{{$post->first_comment->comment}}</p>
                                        </div>
                                        
                                        <footer>
                                            <div class="viewer-opinion">
                                                @if(Session::has('auth'))
                                                    @if($post->first_comment->up)
                                                        <span><i data-id="{{$post->first_comment->id}}" data-username="{{$post->first_comment->username}}" class="flicon flaticon-thumbs-up upvotecomment checked unvote"></i></span>
                                                    @else
                                                        <span><i data-id="{{$post->first_comment->id}}" data-username="{{$post->first_comment->username}}" class="flicon flaticon-thumbs-up upvotecomment up-vote"></i></span>
                                                    @endif
                                                        <span>{{$post->first_comment->points}}</span>
                                                    @if($post->first_comment->down)
                                                        <span><i data-id="{{$post->first_comment->id}}" data-username="{{$post->first_comment->username}}" class="flicon flaticon-thumbs-up downvotecomment checked unvotedown"></i></span>
                                                    @else
                                                        <span><i data-id="{{$post->first_comment->id}}" data-username="{{$post->first_comment->username}}" class="flicon flaticon-thumbs-down downvotecomment votedown"></i></span>
                                                    @endif
                                                @else
                                                    <span><i  class="flicon flaticon-thumbs-up showregisterbox"></i></span>
                                                    <span>{{$post->first_comment->points}}</span>
                                                    <span><i  class="flicon flaticon-thumbs-down showregisterbox"></i></span>
                                                @endif
                                            </div>
                                        </footer>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <a href="/{{$code}}/g/{{$post->p_id}}#comments" class="continue">Read more comments</a>
                    </div>
                    <!-- end of .comment-section -->
                    @endif
                </div>
                @endforeach
                
                @if($posts->getLastPage() > 1)
                <div class="pages">
                    {{$posts->links()}}
                </div>
                @endif
                
           
            </main>
            
            @else
                
            <main class="main-body">
                <div class="no-data">
                    <p>No Posts to show!</p>
                </div>
            </main>
            @endif
        </div>
            
        @include('sidebar')
    </div>
</div>
@stop
