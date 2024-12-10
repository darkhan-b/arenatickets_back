<div class="font-size messages-box">
    {{--<div class="container">--}}
    <div class="">

        @if(Session::has('message'))
            <div id="alert">
                <div class="section">
                    <div class="alert alert-info alert-dismissible">
                        {!! Session::get('message') !!}
                    </div>
                </div>
            </div>
        @endif

        @if (isset($errors) && count($errors) > 0)
            <div id="errors">
                <div class="section">
                    <div class="alert alert-danger alert-dismissible">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>