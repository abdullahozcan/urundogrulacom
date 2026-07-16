<div x-data x-effect="
    let loading = $wire.$loading;
    let existing = document.getElementById('rm-preloader');

    if (loading && !existing) {
        let tab = document.querySelector('.fi-tab:not([style*=&quot;display:none&quot;])');
        if (tab) {
            let div = document.createElement('div');
            div.id = 'rm-preloader';
            div.className = 'absolute inset-0 z-30 flex items-center justify-center bg-white/80 dark:bg-gray-900/80 rounded-xl';
            div.innerHTML = '<div class=&quot;flex flex-col items-center gap-2 p-6&quot;>' +
                '<svg class=&quot;animate-spin h-8 w-8 text-primary-500&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot;>' +
                '<circle class=&quot;opacity-25&quot; cx=&quot;12&quot; cy=&quot;12&quot; r=&quot;10&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;4&quot;/&gt;' +
                '<path class=&quot;opacity-75&quot; fill=&quot;currentColor&quot; d=&quot;M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z&quot;/&gt;' +
                '</svg>' +
                '<span class=&quot;text-sm text-gray-500 dark:text-gray-400 font-medium&quot;>Yükleniyor...</span>' +
                '</div>';
            tab.style.position = 'relative';
            tab.prepend(div);
        }
    } else if (!loading && existing) {
        existing.remove();
    }
"></div>
