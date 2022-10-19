<?php

namespace NBerces\Coerce;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\NoSuchOptionException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Throwable;

/**
 * Class Coerce
 * @package NBerces\Coerce
 */
class Coerce
{
    /**
     * Returns a Boolean value coerced from the given variable, or a
     * default value if coersion fails.
     *
     * The following values will coerce to `true`:
     * - `true`
     * - 1
     * - "1"
     * - "true"
     * - "yes"
     * - "on"
     *
     * The following values will coerce to `false`:
     * - `false`
     * - 0
     * - "0"
     * - "false"
     * - "no"
     * - "off"
     *
     * Leading/trailing whitespace is trimmed from string-based values proir
     * to coersion, and string comparisons are case-insensitive.
     *
     * All other values will fail coersion.
     *
     * The following options **MAY** be supplied:
     * - **default**: Return this value if coersion fails. **Default**: `null`.
     *
     * @param $variable
     * @param array $options
     * @return bool|mixed A Boolean value coerced from the given variable, or a
     * default value if coersion fails.
     */
    public static function toBool($variable, array $options = [])
    {
        $resolver = new OptionsResolver();

        try {
            static::configureToBooleanOptionsResolver($resolver);

            $options = $resolver->resolve($options);
        } catch (AccessException
        |InvalidOptionsException
        |MissingOptionsException
        |NoSuchOptionException
        |OptionDefinitionException
        |UndefinedOptionsException $exception
        ) {
            throw new InvalidArgumentException(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        if (is_bool($variable)) {
            return $variable;
        }

        if (is_int($variable)) {
            switch ($variable) {
                case 0:
                    $variable = false;
                    break;
                case 1:
                    $variable = true;
                    break;
            }
        } elseif (is_string($variable)) {
            $variable = trim($variable);

            if (0 === strcmp('0', $variable)
                || 0 === strcasecmp('false', $variable)
                || 0 === strcasecmp('off', $variable)
                || 0 === strcasecmp('no', $variable)
            ) {
                $variable = false;
            } elseif (0 === strcmp('1', $variable)
                || 0 === strcasecmp('true', $variable)
                || 0 === strcasecmp('on', $variable)
                || 0 === strcasecmp('yes', $variable)
            ) {
                $variable = true;
            }
        }

        if (!is_bool($variable)) {
            return $options['default'];
        }

        return $variable;
    }

    /**
     * Returns an instance of `DateTimeInterface` coerced from the given variable,
     * or a default value if coersion fails.
     *
     * The following values are eligible for coersion:
     * - an existing instance of `DateTimeInterface`.
     * - a date/time string in a valid format supported by PHP (leading/trailing
     * whitespace is trimmed).
     * - an associative array of integers keyed by 'day', 'month', 'year', and
     * optionally 'hour' (default `0`) and 'minute' (default `0`).
     *
     * All other values will fail coersion.
     *
     * The following options **MAY** be supplied:
     * - **default**: Return this value if coersion fails. **Default**: `null`.
     * - **immutable**: Coerce to an instance of `DateTimeImmutable` (`true`), or
     * `DateTime` (`false`|`null`). If `true`, and the given variable is an
     * instance of `DateTime`, return an immutable equivalent. Similarly,
     * if `false`, and the given variable is an instance
     * of `DateTimeImmutable`, return a mutable equivalent. **Default**: `null`.
     * - **tz**: Use this timezone for the resulting instance of `DateTimeInterface`.
     * If the given variable is an instance of `DateTimeInterface`, and no timezone
     * is supplied, use the timezone of the given instance. Otherwise,
     * use the default timezone configured for PHP. Ignore this option
     * if the given variable is a string-based date whose format
     * explicitly identifies a timezone. This option accepts a string-based
     * timezone identifier or an instance of `DateTimeZone`.
     *
     * @param $variable
     * @param array $options
     * @return DateTimeInterface|mixed An instance of DateTimeInterface coerced
     * from the given variable, or a default value if coersion fails.
     * @see DateTimeInterface
     * @see date()
     * @link https://www.php.net/manual/en/datetime.formats.php Date & Time Formats
     */
    public static function toDateTime($variable, array $options = [])
    {
        $resolver = new OptionsResolver();

        try {
            static::configureToDateTimeOptionsResolver($resolver);

            $options = $resolver->resolve($options);
        } catch (AccessException
        |InvalidOptionsException
        |MissingOptionsException
        |NoSuchOptionException
        |OptionDefinitionException
        |UndefinedOptionsException $exception
        ) {
            throw new InvalidArgumentException(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        if ($variable instanceof DateTimeInterface) {
            if (is_null($options['immutable'])) {
                if ($variable instanceof DateTime) {
                    $options['immutable'] = false;
                } elseif ($variable instanceof DateTimeImmutable) {
                    $options['immutable'] = true;
                }
            }

            if (is_null($options['tz'])) {
                $options['tz'] = $variable->getTimezone();
            }

            $variable = $variable->format('Y-m-d H:i:s');
        } elseif (is_array($variable)) {
            $day = null;
            $hour = 0;
            $minute = 0;
            $month = null;
            $year = null;

            extract($variable, EXTR_IF_EXISTS);

            $variable = sprintf(
                '%d-%d-%d %d:%d:00',
                static::toInt($year, ['default' => -1]),
                static::toInt($month, ['default' => 13]),
                static::toInt($day, ['default' => 99]),
                static::toInt($hour, ['default' => 0, 'noGreaterThan' => 23, 'noLessThan' => 0]),
                static::toInt($minute, ['default' => 0, 'noGreaterThan' => 59, 'noLessThan' => 0])
            );
        }

        if (true === $options['immutable']) {
            $type = 'DateTimeImmutable';
        } else {
            $type = 'DateTime';
        }

        try {
            $variable = new $type(
                trim((string)$variable),
                $options['tz']
            );
        } catch (Throwable $throwable) {
            $variable = null;
        }

        if (!($variable instanceof DateTimeInterface)) {
            return $options['default'];
        }

        return $variable;
    }

    /**
     * Returns a string-based e-mail address coerced from the given variable,
     * or a default value if coersion fails.
     *
     * Any value of type `string`, or of a type that can be cast as a `string`,
     * is eligible for coersion. Leading/trailing whitespace is trimmed.
     *
     * All other values will fail coersion.
     *
     * The following options **MAY** be supplied:
     * - **default**: Return this value if coersion fails. **Default**: `null`.
     *
     * @param $variable
     * @param array $options
     * @return string|mixed A string-based email address coerced from the given
     * variable, or a default value if coersion fails.
     */
    public static function toEmailAddress($variable, array $options = [])
    {
        $resolver = new OptionsResolver();

        try {
            static::configureToEmailAddressOptionsResolver($resolver);

            $options = $resolver->resolve($options);
        } catch (AccessException
        |InvalidOptionsException
        |MissingOptionsException
        |NoSuchOptionException
        |OptionDefinitionException
        |UndefinedOptionsException $exception
        ) {
            throw new InvalidArgumentException(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        $variable = static::toString(
            $variable,
            [
                'default' => '',
                'maxLength' => 320,
                'trimWhitespace' => true
            ]
        );

        if (false === filter_var($variable, FILTER_VALIDATE_EMAIL)) {
            return $options['default'];
        }

        return $variable;
    }

    /**
     * Returns a floating point number coerced from the given variable,
     * or a default value if coersion fails.
     *
     * Any numeric value, or any value of a type that can be cast as a number,
     * is eligible for coersion. Leading/trailing whitespace is trimmed from
     * string-based values prior to coersion.
     *
     * All other values will fail coersion.
     *
     * The following options **MAY** be supplied:
     * - **allowZero**: Allow coercing to zero (`true`); otherwise, regard zero as a
     * failed coersion (`false`). **Default**: `true`.
     * - **default**: Return this value if coersion fails. **Default**: `null`.
     * - **noGreaterThan**: Coerce to a `float` no greater than this
     * number. If coersion results in a `float` greater than this number, reduce the
     * resulting `float` to equal this number. This option accepts
     * an `int` or `float`.
     * - **noLessThan**: Coerce to a `float` no less than this
     * number. If coersion results in a `float` that is less than this number,
     * increase the resulting `float` to equal this number. This option accepts
     * an `int` or `float`.
     *
     * @param $variable
     * @param array $options
     * @return float|mixed A floating point number coerced from the given variable,
     * or a default value if coersion fails.
     */
    public static function toFloat($variable, array $options = [])
    {
        $resolver = new OptionsResolver();

        try {
            static::configureToFloatOptionsResolver($resolver);

            $options = $resolver->resolve($options);
        } catch (AccessException
        |InvalidOptionsException
        |MissingOptionsException
        |NoSuchOptionException
        |OptionDefinitionException
        |UndefinedOptionsException $exception
        ) {
            throw new InvalidArgumentException(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        if (is_string($variable)) {
            $variable = trim($variable);
        }

        if (is_numeric($variable)) {
            $variable = (float)$variable;
        }

        if (!is_float($variable)) {
            return $options['default'];
        }

        if (!is_null($options['noGreaterThan'])
            && $variable > $options['noGreaterThan']
        ) {
            $variable = (float)$options['noGreaterThan'];
        }

        if (!is_null($options['noLessThan'])
            && $variable < $options['noLessThan']
        ) {
            $variable = (float)$options['noLessThan'];
        }

        if (0.0 === $variable
            && !$options['allowZero']
        ) {
            return $options['default'];
        }

        return $variable;
    }

    /**
     * Returns an integer coerced from the given variable,
     * or a default value if coersion fails.
     *
     * Any numeric value, or any value of a type that can be cast as a number,
     * is eligible for coersion. Leading/trailing whitespace is trimmed from
     * string-based values prior to coersion.
     *
     * All other values will fail coersion.
     *
     * The following options **MAY** be supplied:
     * - **allowZero**: Allow coercing to zero (`true`); otherwise, regard zero as a
     * failed coersion (`false`). **Default**: `true`.
     * - **default**: Return this value if coersion fails. **Default**: `null`.
     * - **noGreaterThan**: Coerce to an `int` no greater than this
     * number. If coersion results in an `int` greater than this number, reduce the
     * resulting `int` to equal this number. This option accepts
     * an `int` or `float`.
     * - **noLessThan**: Coerce to an `int` no less than this
     * number. If coersion results in an `int` that is less than this number,
     * increase the resulting `int` to equal this number. This option accepts
     * an `int` or `float`.
     *
     * @param $variable
     * @param array $options
     * @return int|mixed An integer coerced from the given variable,
     * or a default value if coersion fails.
     */
    public static function toInt($variable, array $options = [])
    {
        $variable = self::toFloat($variable, $options);

        if (is_float($variable)) {
            return (int)$variable;
        }

        return $variable;
    }

    /**
     * Returns a plain-text string coerced from the given HTML snippet,
     * or a default value if coersion fails.
     *
     * Any string-based value, or any value of a type that can be cast as a string,
     * is eligible for coersion.
     *
     * All other values will fail coersion.
     *
     * The following options **MAY** be supplied:
     * - **allowBlank**: Allow coercing to a blank string (`true`); otherwise,
     * regard a blank string as a failed coersion (`false`). A string is regarded
     * as blank if its length is 0 or if every character in the string creates
     * some sort of white space. **Default**: `true`.
     * - **default**: Return this value if coersion fails. **Default**: `null`.
     * - **compactWhitespace**: Replace all whitespace characters in the resulting
     * `string` with a single space. This option accepts `true`
     * or `false`. **Default**: `false`.
     * - **maxLength**: Coerce to a `string` no greater than this
     * number of characters in length. If coersion results in a `string` greater
     * than this number of characters in length, truncate the resulting `string`.
     * This option accepts an `int`.
     *
     * @param $variable
     * @param array $options
     * @return string|mixed A plain-text string coerced from the given variable,
     * or a default value if coersion fails.
     */
    public static function toPlainText($variable, array $options = [])
    {
        $resolver = new OptionsResolver();

        try {
            static::configureToPlainTextOptionsResolver($resolver);

            $options = $resolver->resolve($options);
        } catch (AccessException
        |InvalidOptionsException
        |MissingOptionsException
        |NoSuchOptionException
        |OptionDefinitionException
        |UndefinedOptionsException $exception
        ) {
            throw new InvalidArgumentException(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        $variable = static::toString($variable, ['default' => '']);

        if (!empty($variable)) {
            $whitespace = '~~@~~';
            /**
             * Add some whitespace to the end of tags, otherwise
             * words run into each other once tags are stripped.
             */
            $variable = str_replace('>', '>' . $whitespace, $variable);
            $variable = strip_tags($variable);
            /**
             * Replace non-breaking space entities with regular spaces
             * before decoding, otherwise we end up with some hard-core
             * UTF-8 hex 0xc2 0xa0 mumbo-jumbo that isn't recognised as
             * white-space and ends up introducing a bunch of blank lines.
             */
            $variable = str_replace('&nbsp;', ' ', $variable);
            $variable = htmlspecialchars_decode($variable, ENT_QUOTES);
            $variable = html_entity_decode($variable, ENT_QUOTES);
            $variable = str_replace(' ', $whitespace, $variable);
            /**
             * Four or more white-spaces in a row means a double line-break
             * (new paragraph). Ensure the 'utf-8' modifier is used.
             */
            $variable = preg_replace('/(\s*' . $whitespace . '\s*){4,}/mu', "\n\n", $variable);
            /**
             * All other whitespace is reduced to a single white-space
             * character.
             */
            $variable = preg_replace('/(\s*' . $whitespace . '\s*)+/mu', ' ', $variable);
            /**
             * Reduce two or more empty lines to a single line-break.
             */
            $variable = preg_replace('/^[[:space:]]{2,}/mu', "\n", $variable);
        }

        return static::toString(
            $variable,
            [
                'allowBlank' => $options['allowBlank'],
                'compactWhitespace' => $options['compactWhitespace'],
                'default' => $options['default'],
                'maxLength' => $options['maxLength'],
                'trimWhitespace' => true
            ]
        );
    }

    /**
     * Returns a string coerced from the given variable,
     * or a default value if coersion fails.
     *
     * Any string-based value, or any value of a type that can be cast as a string,
     * is eligible for coersion.
     *
     * All other values will fail coersion.
     *
     * The following options **MAY** be supplied:
     * - **allowBlank**: Allow coercing to a blank string (`true`); otherwise,
     * regard a blank string as a failed coersion (`false`). A string is regarded
     * as blank if its length is 0 or if every character in the string creates
     * some sort of white space. **Default**: `true`.
     * - **default**: Return this value if coersion fails. **Default**: `null`.
     * - **compactWhitespace**: Replace all whitespace characters in the resulting
     * `string` with a single space. This option accepts `true`
     * or `false`. **Default**: `false`.
     * - **maxLength**: Coerce to a `string` no greater than this
     * number of characters in length. If coersion results in a `string` greater
     * than this number of characters in length, truncate the resulting `string`.
     * This option accepts an `int`.
     * - **trimWhitespace**: Remove leading and trailing whitespace
     * characters from the resulting `string`. This option accepts `true`
     * or `false`. **Default**: `false`.
     *
     * @param $variable
     * @param array $options
     * @return string|mixed A string coerced from the given variable,
     * or a default value if coersion fails.
     */
    public static function toString($variable, array $options = [])
    {
        $resolver = new OptionsResolver();

        try {
            static::configureToStringOptionsResolver($resolver);

            $options = $resolver->resolve($options);
        } catch (AccessException
        |InvalidOptionsException
        |MissingOptionsException
        |NoSuchOptionException
        |OptionDefinitionException
        |UndefinedOptionsException $exception
        ) {
            throw new InvalidArgumentException(
                $exception->getMessage(),
                0,
                $exception
            );
        }

        if (is_numeric($variable)
            ||
            (
                is_object($variable)
                && method_exists($variable, '__toString')
            )
        ) {
            $variable = (string)$variable;
        }

        if (!is_string($variable)) {
            return $options['default'];
        }

        if (!empty($variable)) {
            if ($options['compactWhitespace']) {
                $variable = preg_replace('/\s+/', ' ', $variable);
            }

            if ($options['trimWhitespace']) {
                $variable = trim($variable);
            }

            if (!is_null($options['maxLength'])
                && 0 < $options['maxLength']
            ) {
                $variable = substr($variable, 0, $options['maxLength']);

                if ($options['trimWhitespace']) {
                    /*
                     * Trim again in case truncation has resulted in
                     * trailing whitespace.
                     */
                    $variable = rtrim($variable);
                }
            }
        }

        if (!$options['allowBlank']
            &&
            (
                0 === strlen($variable)
                || ctype_space($variable)
            )
        ) {
            return $options['default'];
        }

        return $variable;
    }

    /**
     * Configures the given resolver to handle options common to most
     * (if not all) coersions.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    protected static function configureCommonOptions(
        OptionsResolver $resolver
    ) {
        $resolver->setDefaults(
            [
                'default' => null
            ]
        );
    }

    /**
     * Configures the given resolver to handle options for `self::toBool()`.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @see Coerce::toBool()
     */
    protected static function configureToBooleanOptionsResolver(
        OptionsResolver $resolver
    ): void {
        static::configureCommonOptions($resolver);
    }

    /**
     * Configures the given resolver to handle options for `self::toDateTime()`.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @see Coerce::toDateTime()
     */
    protected static function configureToDateTimeOptionsResolver(
        OptionsResolver $resolver
    ): void {
        static::configureCommonOptions($resolver);

        $resolver->setDefaults(
            [
                'immutable' => null,
                'tz' => null
            ]
        );

        $resolver->setAllowedTypes('immutable', ['bool', 'null']);
        $resolver->setAllowedTypes('tz', [DateTimeZone::class, 'string', 'null']);

        $resolver->setNormalizer(
            'tz',
            function (Options $options, $value) {
                if (is_string($value)) {
                    $value = new DateTimeZone($value);
                }

                return $value;
            }
        );
    }

    /**
     * Configures the given resolver to handle options for `self::toEmailAddress()`.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @see Coerce::toEmailAddress()
     */
    protected static function configureToEmailAddressOptionsResolver(
        OptionsResolver $resolver
    ): void {
        static::configureCommonOptions($resolver);
    }

    /**
     * Configures the given resolver to handle options for `self::toFloat()`.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @see Coerce::toFloat()
     */
    protected static function configureToFloatOptionsResolver(
        OptionsResolver $resolver
    ): void {
        static::configureCommonOptions($resolver);

        $resolver->setDefaults(
            [
                'allowZero' => true,
                'noGreaterThan' => null,
                'noLessThan' => null
            ]
        );

        $resolver->setAllowedTypes('allowZero', 'boolean');
        $resolver->setAllowedTypes('noGreaterThan', ['null', 'float', 'int']);
        $resolver->setAllowedTypes('noLessThan', ['null', 'float', 'int']);
    }

    /**
     * Configures the given resolver to handle options for `self::toPlainText()`.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @see Coerce::toPlainText()
     */
    protected static function configureToPlainTextOptionsResolver(
        OptionsResolver $resolver
    ): void {
        static::configureCommonOptions($resolver);

        $resolver->setDefaults(
            [
                'allowBlank' => true,
                'compactWhitespace' => false,
                'maxLength' => null
            ]
        );

        $resolver->setAllowedTypes('allowBlank', 'boolean');
        $resolver->setAllowedTypes('compactWhitespace', 'boolean');
        $resolver->setAllowedTypes('maxLength', ['null', 'int']);
    }

    /**
     * Configures the given resolver to handle options for `self::toString()`.
     *
     * @param OptionsResolver $resolver
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @see Coerce::toString()
     */
    protected static function configureToStringOptionsResolver(
        OptionsResolver $resolver
    ): void {
        static::configureCommonOptions($resolver);

        $resolver->setDefaults(
            [
                'allowBlank' => true,
                'compactWhitespace' => false,
                'maxLength' => null,
                'trimWhitespace' => false
            ]
        );

        $resolver->setAllowedTypes('allowBlank', 'boolean');
        $resolver->setAllowedTypes('compactWhitespace', 'boolean');
        $resolver->setAllowedTypes('maxLength', ['null', 'int']);
        $resolver->setAllowedTypes('trimWhitespace', 'boolean');
    }
}
