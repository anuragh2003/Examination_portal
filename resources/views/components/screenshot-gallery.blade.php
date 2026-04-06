<!-- Screenshot Gallery Viewer Component -->
<div class="screenshot-gallery-container" style="background: #1a1a1a; padding: 15px; border-radius: 6px; color: white; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto;">
    <style>
        .gallery-wrapper {
            display: flex;
            flex-direction: column;
            gap: 12px;
            height: 100%;
        }

        .gallery-main {
            flex: 1;
            display: flex;
            gap: 12px;
            min-height: 300px;
        }

        .gallery-display {
            flex: 1;
            background: #000;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .gallery-display img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .gallery-display .no-data {
            color: #666;
            text-align: center;
        }

        .gallery-sidebar {
            width: 160px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .gallery-thumbs {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 4px;
        }

        .gallery-thumb {
            width: 100%;
            aspect-ratio: 16/9;
            border: 2px solid #333;
            border-radius: 3px;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.2s;
        }

        .gallery-thumb:hover,
        .gallery-thumb.active {
            border-color: #0066cc;
            transform: scale(1.03);
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-controls {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
            padding: 8px;
            background: #222;
            border-radius: 4px;
        }

        .gallery-button {
            padding: 6px 10px;
            background: #0066cc;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .gallery-button:hover {
            background: #0052a3;
        }

        .gallery-button:disabled {
            background: #555;
            cursor: not-allowed;
        }

        .gallery-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px;
            background: #222;
            border-radius: 3px;
            font-size: 11px;
            color: #aaa;
        }

        .gallery-timeline {
            background: #222;
            padding: 12px 8px;
            border-radius: 4px;
        }

        .timeline-track {
            width: 100%;
            min-height: 80px;
            height: 80px;
            background: #333;
            border-radius: 3px;
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            overflow-x: auto;
            overflow-y: visible;
            scrollbar-width: thin;
            scrollbar-color: #666 #333;
        }

        .timeline-track::-webkit-scrollbar {
            height: 6px;
        }

        .timeline-track::-webkit-scrollbar-track {
            background: #333;
        }

        .timeline-track::-webkit-scrollbar-thumb {
            background: #666;
            border-radius: 2px;
        }

        .timeline-frame {
            height: 100%;
            min-width: 80px;
            width: 80px;
            background: #0066cc;
            border-right: 1px solid #444;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
            position: relative;
            flex-shrink: 0;
        }

        .timeline-frame:hover {
            opacity: 1;
            background: #0077ff;
        }

        .timeline-frame.active {
            background: #ff6600;
            opacity: 1;
        }

        .timeline-tooltip {
            position: absolute;
            bottom: 88px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 100;
        }

        .timeline-frame:hover .timeline-tooltip {
            opacity: 1;
        }

        .timeline-preview {
            position: absolute;
            bottom: 90px;
            background: black;
            border: 2px solid #0066cc;
            border-radius: 3px;
            width: 120px;
            height: 72px;
            display: none;
            z-index: 100;
        }

        .timeline-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .timeline-label {
            position: absolute;
            color: #666;
            font-size: 9px;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
        }

        .filter-buttons {
            display: flex;
            gap: 6px;
            margin-bottom: 8px;
        }

        .filter-btn {
            padding: 5px 10px;
            background: #333;
            color: white;
            border: 2px solid transparent;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .filter-btn.active {
            background: #0066cc;
            border-color: #0077ff;
        }

        .filter-btn:hover {
            background: #444;
        }
    </style>

    <div class="gallery-wrapper">
        <!-- Filters -->
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="screen">Screen Only</button>
            <button class="filter-btn" data-filter="face">Face Only</button>
        </div>

        <!-- Main Display + Sidebar -->
        <div class="gallery-main">
            <div class="gallery-display">
                <div class="no-data">No screenshots available</div>
            </div>
            <div class="gallery-sidebar">
                <div style="font-size: 11px; color: #999; padding: 0 5px;">Recent Frames</div>
                <div class="gallery-thumbs"></div>
                <div class="gallery-info">
                    <span class="frame-count">0 frames</span>
                    <span class="frame-time">--:--</span>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="gallery-controls">
            <button class="gallery-button" id="prevBtn">← Previous</button>
            <button class="gallery-button" id="playBtn">▶ Play</button>
            <button class="gallery-button" id="nextBtn">Next →</button>
            <span style="margin: 0 10px; color: #999; font-size: 12px;">
                <span id="currentFrame">0</span> / <span id="totalFrames">0</span>
            </span>
        </div>

        <!-- Timeline -->
        <div class="gallery-timeline">
            <div class="timeline-track" id="timelineTrack">
                <div class="timeline-preview" id="timelinePreview"></div>
            </div>
        </div>
    </div>

    <script>
        class ScreenshotGallery {
            constructor(container, imageSources) {
                this.container = container;
                this.imageSources = imageSources || [];
                this.currentIndex = 0;
                this.isPlaying = false;
                this.playSpeed = 500; // ms between frames
                this.filter = 'all';
                this.filteredImages = [];
                this.frameMap = {}; // Original index to filtered index mapping

                this.init();
            }

            init() {
                this.attachEventListeners();
                this.loadImages();
                this.render();
            }

            attachEventListeners() {
                // Filter buttons
                const container = this.getContainer();
                container.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        container.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        this.filter = btn.dataset.filter;
                        this.filterImages();
                        this.currentIndex = 0;
                        this.render();
                    });
                });

                // Navigation buttons
                container.querySelector('#prevBtn').addEventListener('click', () => this.previous());
                container.querySelector('#nextBtn').addEventListener('click', () => this.next());
                container.querySelector('#playBtn').addEventListener('click', () => this.togglePlay());

                // Timeline click
                container.querySelector('#timelineTrack').addEventListener('click', (e) => {
                    this.handleTimelineClick(e);
                });

                // Hide preview when mouse leaves timeline track
                container.querySelector('#timelineTrack').addEventListener('mouseleave', () => {
                    this.hideTimelinePreview();
                });

                // Keyboard shortcuts
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'ArrowLeft') this.previous();
                    if (e.key === 'ArrowRight') this.next();
                    if (e.key === ' ') { e.preventDefault(); this.togglePlay(); }
                });
            }

            getContainer() {
                if (typeof this.container === 'string') {
                    return document.getElementById(this.container);
                }
                return this.container;
            }

            loadImages() {
                this.filteredImages = this.imageSources;
                this.filterImages();
            }

            filterImages() {
                if (this.filter === 'all') {
                    this.filteredImages = this.imageSources;
                } else {
                    this.filteredImages = this.imageSources.filter(img => img.type === this.filter);
                }

                // Rebuild frame map
                this.frameMap = {};
                this.imageSources.forEach((img, idx) => {
                    if (this.filteredImages.includes(img)) {
                        this.frameMap[idx] = this.filteredImages.indexOf(img);
                    }
                });
            }

            render() {
                this.updateDisplay();
                this.updateTimeline();
                this.updateInfo();
            }

            updateDisplay() {
                const container = this.getContainer();
                const display = container.querySelector('.gallery-display');
                const thumbsContainer = container.querySelector('.gallery-thumbs');

                if (this.filteredImages.length === 0) {
                    display.innerHTML = '<div class="no-data">No screenshots available</div>';
                    thumbsContainer.innerHTML = '';
                    return;
                }

                const current = this.filteredImages[this.currentIndex];
                if (current && current.url) {
                    display.innerHTML = `<img src="${current.url}" alt="Screenshot">`;
                }

                // Update thumbnails (show last 5)
                thumbsContainer.innerHTML = '';
                const startIdx = Math.max(0, this.currentIndex - 4);
                const endIdx = Math.min(this.filteredImages.length, this.currentIndex + 1);

                for (let i = startIdx; i < endIdx; i++) {
                    const img = this.filteredImages[i];
                    const thumb = document.createElement('div');
                    thumb.className = 'gallery-thumb' + (i === this.currentIndex ? ' active' : '');
                    thumb.innerHTML = `<img src="${img.url}" alt="Frame ${i}">`;
                    thumb.addEventListener('click', () => {
                        this.currentIndex = i;
                        this.render();
                    });
                    thumbsContainer.appendChild(thumb);
                }
            }

            updateTimeline() {
                const container = this.getContainer();
                const track = container.querySelector('#timelineTrack');
                track.innerHTML = '<div class="timeline-preview" id="timelinePreview"></div>';

                this.filteredImages.forEach((img, idx) => {
                    const frame = document.createElement('div');
                    frame.className = 'timeline-frame' + (idx === this.currentIndex ? ' active' : '');
                    frame.style.backgroundImage = `url('${img.url}')`;
                    frame.style.backgroundSize = 'cover';
                    if (idx === this.currentIndex) {
                        frame.style.width = '100px';
                    }

                    // Add tooltip with timestamp
                    const tooltip = document.createElement('div');
                    tooltip.className = 'timeline-tooltip';
                    tooltip.textContent = img.timestamp || '--:--';
                    frame.appendChild(tooltip);

                    // Add hover preview functionality
                    frame.addEventListener('mouseenter', (e) => {
                        this.showTimelinePreview(e, img, frame);
                    });
                    frame.addEventListener('mouseleave', () => {
                        this.hideTimelinePreview();
                    });

                    frame.addEventListener('click', () => {
                        this.currentIndex = idx;
                        this.render();
                    });
                    track.appendChild(frame);
                });
            }

            updateInfo() {
                const container = this.getContainer();
                container.querySelector('.frame-count').textContent = this.filteredImages.length + ' frames';
                container.querySelector('#currentFrame').textContent = this.currentIndex + 1;
                container.querySelector('#totalFrames').textContent = this.filteredImages.length;
                
                if (this.filteredImages.length > 0) {
                    const current = this.filteredImages[this.currentIndex];
                    container.querySelector('.frame-time').textContent = current.timestamp || '--:--';
                }
            }

            showTimelinePreview(e, img, frame) {
                const container = this.getContainer();
                const preview = container.querySelector('#timelinePreview');
                const track = container.querySelector('#timelineTrack');
                const trackRect = track.getBoundingClientRect();
                const frameRect = frame.getBoundingClientRect();

                preview.innerHTML = `<img src="${img.url}" alt="Preview">`;
                // Position the preview centered above the frame
                const frameCenter = frameRect.left + frameRect.width / 2;
                const previewLeft = frameCenter - trackRect.left - 60; // 60 is half of preview width (120px)
                preview.style.left = Math.max(0, Math.min(previewLeft, trackRect.width - 120)) + 'px';
                preview.style.display = 'block';
            }

            hideTimelinePreview() {
                const container = this.getContainer();
                const preview = container.querySelector('#timelinePreview');
                // Small delay to prevent flickering
                setTimeout(() => {
                    preview.style.display = 'none';
                }, 100);
            }

            previous() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                    this.render();
                }
            }

            next() {
                if (this.currentIndex < this.filteredImages.length - 1) {
                    this.currentIndex++;
                    this.render();
                }
            }

            togglePlay() {
                this.isPlaying = !this.isPlaying;
                const container = this.getContainer();
                const playBtn = container.querySelector('#playBtn');
                if (playBtn) {
                    playBtn.textContent = this.isPlaying ? '⏸ Pause' : '▶ Play';
                }

                if (this.isPlaying) {
                    this.autoPlay();
                }
            }

            autoPlay() {
                if (!this.isPlaying) return;

                this.next();
                if (this.currentIndex === this.filteredImages.length - 1) {
                    this.isPlaying = false;
                    this.container.getElementById('playBtn').textContent = '▶ Play';
                    this.render();
                    return;
                }

                setTimeout(() => this.autoPlay(), this.playSpeed);
            }
        }

        // Initialize gallery when images are provided
        window.initScreenshotGallery = function(containerId, imageSources) {
            const container = document.getElementById(containerId);
            if (container) {
                return new ScreenshotGallery(container, imageSources);
            }
        };
    </script>
</div>
