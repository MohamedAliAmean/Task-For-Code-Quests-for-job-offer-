import './bootstrap';

import Plyr from 'plyr';

window.plyrVideoPlayer = (options = {}) => ({
    player: null,
    started: false,

    init() {
        if (!this.$refs?.video) return;

        this.player = new Plyr(this.$refs.video, options);

        this.player.on('play', () => {
            if (this.started) return;

            this.started = true;

            if (typeof this.$wire?.markStarted === 'function') {
                this.$wire.markStarted();
            }
        });
    },

    destroy() {
        if (!this.player) return;

        this.player.destroy();
        this.player = null;
    },
});
