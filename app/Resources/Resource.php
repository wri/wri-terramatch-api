<?php

namespace App\Resources;

/**
 * This class is the base for all resources. Ideally we should be using
 * Laravel's resources here, but they suffer from the inability to pass in two
 * classes as a base. For our versioned models we need to accept a parent and a
 * child. Laravel can't do this so we've had to write our own.
 *
 * All of the resource classes are POCOs though... so they should be easy to
 * understand.
 */
abstract class Resource
{
}
