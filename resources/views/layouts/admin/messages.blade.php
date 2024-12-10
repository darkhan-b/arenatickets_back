@if(Session::has('message'))
    <div class="container-fluid">
        <div id="alert" class="message-box pointer">

            <div class="section">
                <div class="alert alert-info block-shadow animated bounceIn bounce">
                    {{Session::get('message')}}
                    {{--<span class="ms-4">&times;</span>--}}
                </div>
            </div>
        </div>
    </div>
@endif

@if (isset($errors) && count($errors) > 0)
    <div class="container-fluid">
        <div id="errors" class="message-box pointer">
            <div class="section">
                <div class="alert alert-danger block-shadow animated bounceIn bounce">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}
                            {{--@if($loop->first)<span class="ms-4">&times;</span>@endif--}}
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
@endif
