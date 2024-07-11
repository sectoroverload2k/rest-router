<?php
namespace RestRouter;

use Exceptions/AuthenticationException;
use Exceptions/CustomException;
use Exceptions/DBErrorException;
use Exceptions/EmptyResponseException;
use Exceptions/ForeignKeyException;
use Exceptions/IException;
use Exceptions/InvalidRequestException;
use Exceptions/JWTErrorException;
use Exceptions/LicenseLimitException;
use Exceptions/MissingPermissionException;
use Exceptions/NotFoundException;
use Exceptions/NotImplementedException;
use Exceptions/RateLimitException;
use Exceptions/RestException;
use Exceptions/UnauthenticatedRequestException;
use Exceptions/UnauthorizedAccessException;

abstract class Exceptions {}
