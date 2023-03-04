@extends('backend.admin-master')
@section('site-title')
    {{__('Country')}}
@endsection
@section('style')
    <x-datatable.css />
    <x-bulk-action.css />
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="">
                    <x-msg.error />
                    <x-msg.flash />
                </div>
                <a class="btn btn-info" href="{{ route("admin.mobile.intro.create") }}">{{ __("Create") }}</a>
            </div>
            <div class="col-lg-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{__('List Mobile Slider')}}</h4>
                        <div class="table-wrap table-responsive">
                            <table class="table table-default">
                                <thead>
                                    <th>{{ __("Sl NO") }}</th>
                                    <th>{{ __("Title") }}</th>
                                    <th>{{ __("Description") }}</th>
                                    <th>{{ __("image") }}</th>
                                    <th>{{ __("Action") }}</th>
                                </thead>
                                <tbody>
                                    @foreach($mobileIntros as $slider)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $slider->title }}</td>
                                            <td>{{ $slider->description }}</td>
                                            <td style="width: 120px">
                                                {!! render_image($slider->image) !!}
                                            </td>
                                            <td>
                                                <x-table.btn.swal.delete :route="route('admin.mobile.intro.delete', $slider->id)" />

                                                <a class="btn btn-primary btn-sm btn-xs mb-3 mr-1" href="{{ route("admin.mobile.intro.edit",$slider->id) }}" >
                                                    <i class="ti-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <x-media.js />
    <x-table.btn.swal.js />
@endsection
