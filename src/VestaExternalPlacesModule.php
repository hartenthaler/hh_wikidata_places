<?php

declare(strict_types=1);

namespace Hartenthaler\Webtrees\Module\ExternalPlacesModule;

use Vesta\Hook\HookInterfaces\PrintFunctionsPlaceInterface;

/**
 * Loaded only after Vesta has registered its hook API.
 *
 * Keeping this adapter separate lets the base module load safely on systems
 * where Vesta Shared Places is absent or uses an older API.
 */
final class VestaExternalPlacesModule extends ExternalPlacesModule implements PrintFunctionsPlaceInterface
{
}
