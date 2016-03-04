<?php
namespace Mopsis\Extensions;

abstract class TagRenderMode
{
    const END_TAG = 2;

    const NORMAL = 0;

    const SELF_CLOSING = 3;

    const START_TAG = 1;
}
