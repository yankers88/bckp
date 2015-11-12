@extends('admin.authenticated.dashboard')

@section('admincontent')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Dashboard
        <small>Remove User Account</small> 
    </h1>
    <ol class="breadcrumb">
        <li><a href="/9gag-admin"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Delete Account</li>
    </ol>
</section>
<!-- Main content -->
<section class="content">
    <!-- Small boxes (Stat box) -->
    <div class="row details-wrapper">
        <div class="search">
            <form class="form-inline search-delete-form">
              <div class="form-group">
                
                <div class="input-group">
                  <div class="input-group-addon">@</div>
                  <input type="text" class="form-control" id="search-delete-username" placeholder="Username">
                  
                </div>
              </div>
              <button type="submit" class="btn btn-primary delete-user">Search</button>
            </form>
        </div>
        
        
    </div>
    <!-- /.row -->
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <section class="col-lg-7 connectedSortable">
        </section>
        <!-- /.Left col -->
    </div>
    <!-- /.row (main row) -->
</section>
<!-- /.content -->
@stop