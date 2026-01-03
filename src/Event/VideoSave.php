<?php

    namespace QCubed\Plugin\Event;

    use QCubed\Event\EventBase;

    /**
     * Class VideoSave
     *
     * Captures the save event that occurs after the popup is closed.
     *
     */

    class VideoSave extends EventBase {

        const string EVENT_NAME = 'videosave';
        const string JS_RETURN_PARAM = 'ui';
    }
