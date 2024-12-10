@extends('layouts.app', ['min' => true])

@section('content')
    <div class="container login">
        <div class="flex-container text-center mt-5">
            <div class="row">
                <div class="col-lg-4 offset-lg-4 col-md-6 offset-md-3">
                    <div class="w-100">
                        <div class="mb-20">
                            @include('parts.logo')
                        </div>

                        <form method="POST" class="mt-5 mx-auto" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-20">

                                <div class="mb-3">
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                    @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Пароль" name="password" required autocomplete="current-password">

                                    @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mb-0 mt-5">
                                <button type="submit" class="btn btn-themed">
                                    {{ __('login') }}
                                </button>


                            </div>
                        </form>
                    </div>
                </div>
            </div>
            


        </div>
    </div>
@endsection
