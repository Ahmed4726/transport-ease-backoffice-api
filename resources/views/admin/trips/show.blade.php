@extends('admin.layouts.app')

@section('title', 'Trip Detail')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Trip Detail</h2>
            <p class="text-muted">View trip route, stops, and driver assignment.</p>
        </div>
        <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary">Back to trips</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h5 class="card-title">Trip summary</h5>
                    <dl class="row">
                        <dt class="col-5">Driver</dt>
                        <dd class="col-7">{{ $trip->driver->user->name ?? 'N/A' }}</dd>
                        <dt class="col-5">Vehicle</dt>
                        <dd class="col-7">{{ $trip->vehicle->brand ?? '' }} {{ $trip->vehicle->model ?? '' }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7"><span class="badge bg-{{ $trip->status === 'scheduled' ? 'secondary' : ($trip->status === 'started' ? 'info' : ($trip->status === 'completed' ? 'success' : 'danger')) }}">{{ ucfirst($trip->status) }}</span></dd>
                        <dt class="col-5">Seats</dt>
                        <dd class="col-7">{{ $trip->available_seats }} / {{ $trip->total_seats }}</dd>
                        <dt class="col-5">Stops</dt>
                        <dd class="col-7">{{ $trip->stops->count() }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title">Stops</h5>
                    @if($trip->stops->isNotEmpty())
                        <ol class="list-group list-group-numbered">
                            @foreach($trip->stops as $tripStop)
                                <li class="list-group-item">
                                    <strong>{{ $tripStop->stop?->location_name ?: $tripStop->stop?->address ?: 'Stop ' . $tripStop->stop_order }}</strong><br>
                                    <small>{{ $tripStop->stop?->address }}</small><br>
                                    <small class="text-muted">Lat: {{ $tripStop->stop?->latitude ?? 'N/A' }}, Lng: {{ $tripStop->stop?->longitude ?? 'N/A' }}</small>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <p class="text-muted">No stops have been recorded for this trip.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title">Trip map</h5>
                    <div id="tripMap" style="height: 520px; min-height: 320px;" class="rounded-3 border"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@php
    $tripStops = $trip->stops->map(function ($tripStop) {
        return [
            'lat' => $tripStop->stop?->latitude,
            'lng' => $tripStop->stop?->longitude,
            'name' => $tripStop->stop?->location_name ?: $tripStop->stop?->address ?: 'Stop ' . $tripStop->stop_order,
            'address' => $tripStop->stop?->address,
        ];
    })->filter(function ($stop) {
        return !empty($stop['lat']) && !empty($stop['lng']);
    })->values();
@endphp

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stops = @json($tripStops);

            const mapContainer = document.getElementById('tripMap');
            if (!mapContainer || stops.length === 0) {
                return;
            }

            const bounds = new google.maps.LatLngBounds();
            const map = new google.maps.Map(mapContainer, {
                zoom: 12,
                center: { lat: stops[0].lat, lng: stops[0].lng },
            });

            const markers = stops.map((stop, index) => {
                const marker = new google.maps.Marker({
                    position: { lat: stop.lat, lng: stop.lng },
                    map,
                    label: `${index + 1}`,
                });

                bounds.extend(marker.position);
                const info = new google.maps.InfoWindow({
                    content: `<strong>${stop.name}</strong><br>${stop.address}`,
                });
                marker.addListener('click', () => info.open(map, marker));
                return marker;
            });

            if (stops.length > 1) {
                const routePath = new google.maps.Polyline({
                    path: stops.map(stop => ({ lat: stop.lat, lng: stop.lng })),
                    strokeColor: '#0088ff',
                    strokeOpacity: 0.8,
                    strokeWeight: 4,
                });
                routePath.setMap(map);
            }

            map.fitBounds(bounds);
        });
    </script>
@endsection
