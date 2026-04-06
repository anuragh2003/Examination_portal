@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding: 15px; max-width: 1100px;">
    <!-- Header -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('submitted.exams') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 10px;">
            ← Back to Submissions
        </a>
        <h1 style="margin: 0; font-size: 24px; color: #333;">Proctor Screenshots</h1>
        <p style="color: #666; margin-top: 3px; font-size: 14px;">{{ $exam->name }} - {{ $student->candidate_name }}</p>
    </div>

    <!-- Student Info Card -->
    <div class="card" style="margin-bottom: 20px; border-left: 4px solid #0066cc;">
        <div class="card-body" style="padding: 15px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <p style="margin: 0; color: #666; font-size: 11px; font-weight: 600; text-transform: uppercase;">Student</p>
                    <p style="margin: 3px 0 0 0; font-size: 14px; font-weight: 600;">{{ $student->candidate_name }}</p>
                </div>
                <div>
                    <p style="margin: 0; color: #666; font-size: 11px; font-weight: 600; text-transform: uppercase;">Email</p>
                    <p style="margin: 3px 0 0 0; font-size: 13px;">{{ $student->candidate_email }}</p>
                </div>
                <div>
                    <p style="margin: 0; color: #666; font-size: 11px; font-weight: 600; text-transform: uppercase;">Total Frames</p>
                    <p style="margin: 3px 0 0 0; font-size: 16px; font-weight: 700; color: #0066cc;">{{ count($imageSources) }}</p>
                </div>
                <div>
                    <p style="margin: 0; color: #666; font-size: 11px; font-weight: 600; text-transform: uppercase;">Screen/Face</p>
                    <p style="margin: 3px 0 0 0; font-size: 14px; font-weight: 600;">
                        <span style="color: #28a745;">{{ count(array_filter($imageSources, fn($img) => $img['type'] === 'screen')) }}</span> /
                        <span style="color: #fd7e14;">{{ count(array_filter($imageSources, fn($img) => $img['type'] === 'face')) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Container -->
    <div class="card">
        <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 12px 15px;">
            <h5 style="margin: 0; color: #333; font-size: 16px;">Screenshot Gallery</h5>
            <p style="margin: 3px 0 0 0; color: #666; font-size: 12px;">Browse captured screenshots • Use arrow keys or buttons to navigate</p>
        </div>
        <div class="card-body" style="padding: 15px; background: #f5f5f5;">
            @if(count($imageSources) > 0)
                <div id="galleryContainer" data-image-sources='@json($imageSources)' style="background: white; border-radius: 6px;">
                    @include('components.screenshot-gallery')
                </div>
            @else
                <div style="text-align: center; padding: 30px; background: white; border-radius: 6px;">
                    <p style="font-size: 16px; color: #999; margin: 0;">
                        📸 No screenshots captured for this exam session
                    </p>
                    <p style="color: #ccc; margin-top: 8px; font-size: 13px;">Screenshots will appear here once the student completes the exam.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Statistics -->
    @if(count($imageSources) > 0)
    <div class="card" style="margin-top: 20px;">
        <div class="card-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 12px 15px;">
            <h5 style="margin: 0; color: #333; font-size: 16px;">Statistics</h5>
        </div>
        <div class="card-body" style="padding: 15px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                <div style="padding: 12px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #0066cc;">
                    <p style="margin: 0; color: #666; font-size: 11px;">Total Frames</p>
                    <p style="margin: 3px 0 0 0; font-size: 18px; font-weight: 700;">{{ count($imageSources) }}</p>
                </div>
                <div style="padding: 12px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #28a745;">
                    <p style="margin: 0; color: #666; font-size: 11px;">Screen Captures</p>
                    <p style="margin: 3px 0 0 0; font-size: 18px; font-weight: 700;">{{ count(array_filter($imageSources, fn($img) => $img['type'] === 'screen')) }}</p>
                </div>
                <div style="padding: 12px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #fd7e14;">
                    <p style="margin: 0; color: #666; font-size: 11px;">Face Captures</p>
                    <p style="margin: 3px 0 0 0; font-size: 18px; font-weight: 700;">{{ count(array_filter($imageSources, fn($img) => $img['type'] === 'face')) }}</p>
                </div>
                <div style="padding: 12px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #17a2b8;">
                    <p style="margin: 0; color: #666; font-size: 11px;">Capture Interval</p>
                    <p style="margin: 3px 0 0 0; font-size: 16px; font-weight: 700;">1.5s</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
    // Try to initialize immediately, and also on DOMContentLoaded
    function initGallery() {
        var gallery = document.getElementById('galleryContainer');
        if (!gallery) return false;

        var imageSourcesRaw = gallery.getAttribute('data-image-sources');
        if (!imageSourcesRaw) return false;

        var imageSources;
        try {
            imageSources = JSON.parse(imageSourcesRaw);
        } catch (error) {
            console.error('Failed to parse screenshot image data:', error);
            return false;
        }

        if (!Array.isArray(imageSources) || imageSources.length === 0) return false;

        if (typeof window.initScreenshotGallery === 'function') {
            window.initScreenshotGallery('galleryContainer', imageSources);
            return true;
        }

        return false;
    }

    // Try immediately
    if (!initGallery()) {
        // If not ready, wait for DOMContentLoaded
        document.addEventListener('DOMContentLoaded', initGallery);
    }
</script>

<style>
    .container-fluid {
        max-width: 1000px;
        margin: 0 auto;
    }
</style>
@endsection
