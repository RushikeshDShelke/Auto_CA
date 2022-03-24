function onMagicTap(e) {
    var mzObj = e.getTarget(),
        tap = {
            id: e.changedTouches[0].identifier,
            ts: e.timeStamp,
            x: e.changedTouches[0].clientX,
            y: e.changedTouches[0].clientY
        },
        tap0;
    while ( mzObj && 'a' !== mzObj.tagName.toLowerCase() ) {
        mzObj = mzObj.parentNode;
    }
    if (!mzObj) {
        return;
    }

    if ( 'touchstart' == e.type ) {
        mzObj.tap = tap;
    } else if ( 'touchend' == e.type ) {
        tap0 = mzObj.tap;
        mzObj.tap = null;
        if (!tap0) {
            return;
        };

        if (tap.id === tap0.id && tap.ts - tap0.ts <= 200
            && Math.sqrt(Math.pow(tap.x-tap0.x,2) + Math.pow(tap.y-tap0.y,2)) <= 15
        ) {
            e.stop();
            if (mzObj.onclick) {
                mzObj.onclick();
            };
            return;
        }
    }
}