@extends('backend.admin-master')
@section('site-title')
    {{__('Country')}}
@endsection
@section('style')
    <x-media.css />
    <x-datatable.css />
    <x-bulk-action.css />
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="">
                    <x-msg.error/>
                    <x-msg.flash/>
                </div>
                <a class="btn btn-info" href="{{ route("admin.mobile.intro.all") }}">List</a>
            </div>
            <div class="col-lg-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{__('Add new Mobile intro')}}</h4>
                        <form action="{{ route("admin.mobile.intro.create") }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input class="form-control" id="title" name="title"
                                       placeholder="Mobile intro Title..."/>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description"
                                          placeholder="Mobile intro Description..."></textarea>
                            </div>

                            <x-media-upload :title="__('Image')" :name="'image_id'" :dimentions="'1280x1280'"/>

                            <div class="form-group">
                                <button class="btn btn-info">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-media.markup />
@endsection
@section('script')
    <x-media.js />
@endsection
