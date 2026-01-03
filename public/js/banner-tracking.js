/**
 * Banner Tracking JavaScript
 * Tracks banner impressions and clicks
 */

// Banner tracking functions
function trackBannerImpression(bannerId) {
    if (!bannerId) return;
    
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
    fetch(baseUrl + 'api/banner_track.php?action=impression&id=' + encodeURIComponent(bannerId))
        .catch(err => console.error('Error tracking impression:', err));
}

function trackBannerClick(element) {
    const bannerId = element.closest('[data-banner-id]')?.getAttribute('data-banner-id');
    if (!bannerId) return true;
    
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
    fetch(baseUrl + 'api/banner_track.php?action=click&id=' + encodeURIComponent(bannerId))
        .catch(err => console.error('Error tracking click:', err));
    
    return true;
}

// Track impressions on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-banner-id]').forEach(function(banner) {
        const bannerId = banner.getAttribute('data-banner-id');
        if (bannerId) {
            trackBannerImpression(bannerId);
        }
    });
});
