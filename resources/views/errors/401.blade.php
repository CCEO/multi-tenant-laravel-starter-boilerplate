@extends('tenant.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">   
                    <h4>401 Unauthorised</h4>
                </div>
                <div class="card-body">
                    You are not authorised to visit this page. <br/><br/>
                    Get in touch with the administrator in case you feel this is an error <br/>
                    Or click the button below to go back to the Homepage.<br/>
                    <hr>
                    <div>
                        <a class="btn btn-info btn-sm" href="/home">
                                <i class="fa fa-arrow-left"></i>
                                Go back to Homepage                        
                            </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection