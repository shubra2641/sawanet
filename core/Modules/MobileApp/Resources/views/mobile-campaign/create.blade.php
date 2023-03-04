@extends('backend.admin-master')
@section('site-title')
    {{__('Country')}}
@endsection
@section('style')
    <x-media.css />
    <x-datatable.css />
    <x-bulk-action.css />
    <x-niceselect.css />
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40">
                    <x-msg.error/>
                    <x-msg.flash/>
                </div>
            </div>
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{__('Add new mobile slider')}}</h4>
                        <form action="{{ route("admin.mobile.campaign.update") }}" method="post">
                            @csrf
                            <div class="form-group" id="product-list">
                                <label for="products">Select Campaign</label>
                                <select id="products" name="campaign" class="form-control">
                                    <option value="">Select Campaign</option>
                                    @foreach($campaigns as $item)
                                        <option value="{{ $item->id }}" {{ $item->id == optional($selectedCampaign)->campaign_id ?? '' ? "selected" : "" }}>{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <button class="btn btn-info">Update Campaign</button>
                            </div>
                        </form>
                    </div>
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
