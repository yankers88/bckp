@extends('index')


@section('title')
    <title>{{MetaData::where('type','=','details')->first()->title}} - Subscription Summary</title>
@stop
<?php 
?>
@section('search')
<div class="post-container">
    <div class="row">
        <div class="col-615">
            <main class="main-body">
                <div class="suggestions">
                    
                    <?php
                        $user           =   Session::get('auth')['username'];
                        $userid         =   Session::get('auth')['id'];
                        $follower_ids   =   Follow::where('user_id','=',$userid)->groupBy('follower_id')->lists('follower_id');
                        foreach($follower_ids as $follower_id){
                            $dom  = Follow::where('follower_id','=',$follower_id)->where('user_id','=',$userid)->join('users','users.id','=','followers.follower_id')->get();
                            $p_details  =   User::where('id','=',$follower_id)->first();
                            $code = Language::where('code','=',$p_details['language'])->first()->code;
			      ?>
    
                    <div class="panel panel-default">
	                    <div class="panel-body">      
                            
                            <div class="header">
                    			<h2><a target="_blank" href="/{{$code}}/g/{{$follower_id}}">{{$p_details->title}}</a></h3>
                            </div>
                            
                            @foreach($dom as $sug)
                                <div class="single">
                                   <!-- <div class="tags">
                            			<h4>Suggestion : <a target="_blank" href="/hashtag/{{$sug->tags}}">{{$sug->tags}}</a></h4>
                            		</div> -->
                                    <div class="users">
                            			<h5>
                            			Followed By : <span class="label label-default"><a target="_blank" href="/s/{{$sug->username}}">{{$sug->username}}</a></span>
                            			</h5>
           
                            		</div>
                            		
                            		<div class="actions">
          <a href="/post/subscriptions/accept">Accept</a>
         <a href="/post/subscriptions/decline">Decline</a>
                            		</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <?php            
                        }
                    ?>
                    
                    <center>@if(!count($follower_ids))No New Suggestions.@endif</center>
                </div>
            </main>
        </div>
            
        @include('sidebar')
    </div>
</div>
@stop
