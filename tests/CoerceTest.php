<?php

namespace NBerces\CoerceTests;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use NBerces\Coerce\Coerce;
use PHPUnit\Framework\TestCase;

/**
 * Class CoerceTest
 * @package NBerces\CoerceTests
 */
class CoerceTest extends TestCase
{
    public function provideCoercesToBoolData()
    {
        return [
            [true, true],
            [1, true],
            ['1', true],
            ['yes', true],
            ['Yes', true],
            ['YES', true],
            [' 1', true],
            ['yes ', true],
            ['  Yes  ', true],
            [false, false],
            [0, false],
            ['0', false],
            ['no', false],
            ['No', false],
            ['NO', false],
            ['   0', false],
            ['no   ', false],
            ['   No   ', false]
        ];
    }

    public function provideCoercesToDateTimeData()
    {
        $expectedTZ = new DateTimeZone(
            date_default_timezone_get()
        );

        return [
            /*
             * An instance of `DateTime` as input is coerced into an instance of
             * `DateTime`.
             */
            [
                new DateTime('1978-10-01 06:30:00'),
                new DateTime('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * An instance of `DateTimeImmutable` as input is coerced into an instance of
             * `DateTimeImmutable`.
             */
            [
                new DateTimeImmutable('1978-10-01 06:30:00'),
                new DateTimeImmutable('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * A string-based date/time as input is coerced into an instance of
             * `DateTime`.
             */
            [
                '1978-10-01 06:30:00',
                new DateTime('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * A string-based date/time with leading/trailing whitespace as input
             * is coerced into an instance of `DateTime`.
             */
            [
                '   1978-10-01 06:30:00',
                new DateTime('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * A string-based date/time with leading/trailing whitespace as input
             * is coerced into an instance of `DateTime`.
             */
            [
                '1978-10-01 06:30:00   ',
                new DateTime('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * A string-based date/time with leading/trailing whitespace as input
             * is coerced into an instance of `DateTime`.
             */
            [
                '   1978-10-01 06:30:00    ',
                new DateTime('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * An array-based date/time using integers as input is coerced into an instance of
             * `DateTime`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978,
                    'hour' => 6,
                    'minute' => 30
                ],
                new DateTime('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * An array-based date/time using strings as input is coerced into an instance of
             * `DateTime`.
             */
            [
                [
                    'day' => '1',
                    'month' => '10  ',
                    'year' => '   1978',
                    'hour' => '  6  ',
                    'minute' => '30'
                ],
                new DateTime('1978-10-01 06:30:00', $expectedTZ)
            ],
            /*
             * An array-based date/time with `minute` missing as input is
             * coerced into an instance of `DateTime` with minute = `00`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978,
                    'hour' => 6
                ],
                new DateTime('1978-10-01 06:00:00', $expectedTZ)
            ],
            /*
             * An array-based date/time with `hour` missing as input is
             * coerced into an instance of `DateTime` with hour = `00`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978,
                    'minute' => 30
                ],
                new DateTime('1978-10-01 00:30:00', $expectedTZ)
            ],
            /*
             * An array-based date/time with `hour` and `minute` missing as input
             * is coerced into an instance of `DateTime` with time = midnight.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978
                ],
                new DateTime('1978-10-01 00:00:00', $expectedTZ)
            ],
            /*
             * An array-based date/time with an invalid `hour` is coerced into an instance of
             * `DateTime` with `hour` == `00`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978,
                    'hour' => -10,
                    'minute' => 30
                ],
                new DateTime('1978-10-01 00:30:00', $expectedTZ)
            ],
            /*
             * An array-based date/time with an invalid `hour` is coerced into an instance of
             * `DateTime` with `hour` == `23`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978,
                    'hour' => 24,
                    'minute' => 30
                ],
                new DateTime('1978-10-01 23:30:00', $expectedTZ)
            ],
            /*
             * An array-based date/time with an invalid `minute` is coerced into an instance of
             * `DateTime` with `minute` == `00`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978,
                    'hour' => 6,
                    'minute' => -30
                ],
                new DateTime('1978-10-01 06:00:00', $expectedTZ)
            ],
            /*
             * An array-based date/time with an invalid `minute` is coerced into an instance of
             * `DateTime` with `minute` == `59`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978,
                    'hour' => 6,
                    'minute' => 78
                ],
                new DateTime('1978-10-01 06:59:00', $expectedTZ)
            ]
        ];
    }

    public function provideCoercesToDateTimeUsingImmutableOptionData()
    {
        return [
            /*
             * An instance of `DateTime` as input with `NULL` as the `immutable`
             * option is coerced into an instance of `DateTime`.
             */
            [
                new DateTime('1978-10-01 06:30:00'),
                null,
                new DateTime('1978-10-01 06:30:00')
            ],
            /*
             * An instance of `DateTime` as input with `false` as the `immutable`
             * option is coerced into an instance of `DateTime`.
             */
            [
                new DateTime('1978-10-01 06:30:00'),
                false,
                new DateTime('1978-10-01 06:30:00')
            ],
            /*
             * An instance of `DateTime` as input with `true` as the `immutable`
             * option is coerced into an instance of `DateTimeImmutable`.
             */
            [
                new DateTime('1978-10-01 06:30:00'),
                true,
                new DateTimeImmutable('1978-10-01 06:30:00')
            ],
            /*
             * An instance of `DateTimeImmutable` as input with `NULL` as the `immutable`
             * option is coerced into an instance of `DateTimeImmutable`.
             */
            [
                new DateTimeImmutable('1978-10-01 06:30:00'),
                null,
                new DateTimeImmutable('1978-10-01 06:30:00')
            ],
            /*
             * An instance of `DateTimeImmutable` as input with `false` as the `immutable`
             * option is coerced into an instance of `DateTime`.
             */
            [
                new DateTimeImmutable('1978-10-01 06:30:00'),
                false,
                new DateTime('1978-10-01 06:30:00')
            ],
            /*
             * An instance of `DateTimeImmutable` as input with `true` as the `immutable`
             * option is coerced into an instance of `DateTimeImmutable`.
             */
            [
                new DateTimeImmutable('1978-10-01 06:30:00'),
                true,
                new DateTimeImmutable('1978-10-01 06:30:00')
            ],
            /*
             * A string-based date/time as input with `NULL` as the `immutable`
             * option is coerced into an instance of `DateTime`.
             */
            [
                '1978-10-01 06:30:00',
                null,
                new DateTime('1978-10-01 06:30:00')
            ],
            /*
             * A string-based date/time as input with `false` as the `immutable`
             * option is coerced into an instance of `DateTime`.
             */
            [
                '1978-10-01 06:30:00',
                false,
                new DateTime('1978-10-01 06:30:00')
            ],
            /*
             * A string-based date/time as input with `true` as the `immutable`
             * option is coerced into an instance of `DateTimeImmutable`.
             */
            [
                '1978-10-01 06:30:00',
                true,
                new DateTimeImmutable('1978-10-01 06:30:00')
            ],
            /*
             * An array-based date/time as input with `NULL` as the `immutable`
             * option is coerced into an instance of `DateTime`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978
                ],
                null,
                new DateTime('1978-10-01 00:00:00')
            ],
            /*
             * An array-based date/time as input with `false` as the `immutable`
             * option is coerced into an instance of `DateTime`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978
                ],
                false,
                new DateTime('1978-10-01 00:00:00')
            ],
            /*
             * An array-based date/time as input with `true` as the `immutable`
             * option is coerced into an instance of `DateTimeImmutable`.
             */
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'year' => 1978
                ],
                true,
                new DateTimeImmutable('1978-10-01 00:00:00')
            ]
        ];
    }

    public function provideCoercesToDateTimeUsingTimezoneOptionData()
    {
        $dttmString = '2000-12-31 11:59:00';
        $southPoleDttmString = '2000-12-31T11:59:00+13:00';

        $defaultTZString = date_default_timezone_get();
        $southPoleTZString = 'Antarctica/South_Pole';

        $defaultTZ = new DateTimeZone($defaultTZString);
        $southPoleTZ = new DateTimeZone($southPoleTZString);

        return [
            /*
             * A string-based date/time as input with a string-based timezone
             * identifier as the `tz` option is coerced into an instance of
             * `DateTime` with a timezone matching the `tz` option.
             */
            [
                $dttmString,
                $southPoleTZString,
                new DateTime($dttmString, $southPoleTZ)
            ],
            /*
             * A string-based date/time as input with `NULL` as the `tz` option
             * is coerced into an instance of `DateTime` with PHP's default
             * timezone.
             */
            [
                $dttmString,
                null,
                new DateTime($dttmString, $defaultTZ)
            ],
            /*
             * An instance of `DateTime` as input with a string-based timezone
             * identifier as the `tz` option is coerced into an instance of
             * `DateTime` with a timezone matching the `tz` option.
             */
            [
                new DateTime($dttmString, $defaultTZ),
                $southPoleTZString,
                new DateTime($dttmString, $southPoleTZ)
            ],
            /*
             * A string-based date/time in a format that explicitly identifies
             * a timezone as input with a string-based timezone
             * identifier as the `tz` option is coerced into an instance of
             * `DateTime` with a timezone matching the format (i.e. the `tz` option
             * is ignored).
             */
            [
                $southPoleDttmString,
                $defaultTZString,
                new DateTime($dttmString, $southPoleTZ)
            ],
            /*
             * An instance of `DateTime` as input with `NULL` as the `tz` option
             * is coerced into an instance of `DateTime` with a timezone matching
             * the input's timezone.
             */
            [
                new DateTime($dttmString, $southPoleTZ),
                null,
                new DateTime($dttmString, $southPoleTZ)
            ]
        ];
    }

    public function provideCoercesToEmailAddressData()
    {
        return [
            ['test@test.com', 'test@test.com'],
            ['   test@test.com', 'test@test.com'],
            ['test@test.com  ', 'test@test.com'],
            ['  test@test.com   ', 'test@test.com']
        ];
    }

    public function provideCoercesToFloatData()
    {
        return [
            [0, 0.0],
            [56, 56.0],
            [-2, -2.0],
            [975.32, 975.32],
            [-32.114, -32.114],
            ['92', 92.0],
            ['873.432', 873.432],
            ['-17.54', -17.54],
            ['  92', 92.0],
            ['873.432  ', 873.432],
            ['  -17.54  ', -17.54]
        ];
    }

    public function provideCoercesToFloatUsingNoGreaterThanOptionData()
    {
        return [
            [0, -10, -10.0],
            [56, 11.234, 11.234],
            [-2, -5.1, -5.1],
            [975.32, 45.10, 45.10],
            [-32.114, -32.120, -32.120],
            ['92', 90, 90.0],
            ['873.432', 873.0, 873.0],
            ['-17.54', -17.0, -17.54],
            ['  92', 93, 92.0],
            ['873.432  ', 873.5, 873.432],
            ['  -17.54  ', -18, -18.0]
        ];
    }

    public function provideCoercesToFloatUsingNoLessThanOptionData()
    {
        return [
            [0, 10, 10.0],
            [56, 56.001, 56.001],
            [-2, -5.1, -2.0],
            [975.32, 45.10, 975.32],
            [-32.120, -32.114, -32.114],
            ['92', 102, 102.0],
            ['873.432', 873.0, 873.432],
            ['-17.54', -17.0, -17.0],
            ['  92', 91, 92.0],
            ['873.432  ', 873.5, 873.5],
            ['  -17.54  ', -18, -17.54]
        ];
    }

    public function provideCoercesToIntData()
    {
        return [
            [0, 0],
            [56, 56],
            [-2, -2],
            [975.32, 975],
            [-32.114, -32],
            ['92', 92],
            ['873.432', 873],
            ['-17.54', -17],
            ['  92', 92],
            ['873.432  ', 873],
            ['  -17.54  ', -17]
        ];
    }

    public function provideCoercesToIntUsingNoGreaterThanOptionData()
    {
        return [
            [0, -10, -10],
            [56, 11.234, 11],
            [-2, -5.1, -5],
            [975.32, 45.10, 45],
            [-32.114, -32.120, -32],
            ['92', 90, 90],
            ['873.432', 873.0, 873],
            ['-17.54', -17.0, -17],
            ['  92', 93, 92],
            ['873.432  ', 873.5, 873],
            ['  -17.54  ', -18, -18]
        ];
    }

    public function provideCoercesToIntUsingNoLessThanOptionData()
    {
        return [
            [0, 10, 10],
            [56, 56.001, 56],
            [-2, -5.1, -2],
            [975.32, 45.10, 975],
            [-32.120, -32.114, -32],
            ['92', 102, 102],
            ['873.432', 873.0, 873],
            ['-17.54', -17.0, -17],
            ['  92', 91, 92],
            ['873.432  ', 873.5, 873],
            ['  -17.54  ', -18, -17]
        ];
    }

    public function provideCoercesToPlainTextData()
    {
        return [
            [
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            ],
            [
                '   Lorem ipsum dolor sit amet, consectetur adipiscing elit.  ',
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            ],
            [
            '  <p>Lorem ipsum &nbsp; dolor sit amet,  </p><p><i>consectetur</i>&nbsp;adipiscing elit. ',
                "Lorem ipsum dolor sit amet,\n\nconsectetur adipiscing elit."
            ],
            [
                "<p>Lorem ipsum dolor sit amet.</p><p></p><p></p><p>Consectetur adipiscing elit.</p>",
                "Lorem ipsum dolor sit amet.\n\nConsectetur adipiscing elit."
            ],
            [
                "<p>Lorem ipsum dolor sit amet.</p><p></p><p></p><p>\n \n \n \n\n <p>Consectetur adipiscing elit.</p>",
                "Lorem ipsum dolor sit amet.\n\nConsectetur adipiscing elit."
            ],
            [
                "\n \n \n<h1>Hello\n \n \n<p>World!</p>",
                'Hello World!'
            ]
        ];
    }

    public function provideCoercesToStringData()
    {
        return [
            [0, '0'],
            [-2, '-2'],
            [975.32, '975.32'],
            [-32.114, '-32.114'],
            [
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
            ],
            [
                '   Lorem ipsum dolor sit amet, consectetur adipiscing elit.  ',
                '   Lorem ipsum dolor sit amet, consectetur adipiscing elit.  '
            ]
        ];
    }

    public function provideCoercesToStringUsingCompactWhitespaceOptionData()
    {
        return [
            ['', true, ''],
            ['', false, ''],
            ['  ', true, ' '],
            ['  ', false, '  '],
            [
                "Lorem   ipsum dolor  \t  sit amet\n, consectetur\n\nadipiscing elit.",
                true,
                'Lorem ipsum dolor sit amet , consectetur adipiscing elit.',
            ],
            [
                ' Lorem ipsum dolor  sit amet, consectetur adipiscing elit. ',
                true,
                ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. '
            ]
        ];
    }

    public function provideCoercesToStringUsingMaxLengthOptionData()
    {
        return [
            ['', null, ''],
            ['  ', null, '  '],
            ['  ', 0, '  '],
            ['  ', 1, ' '],
            ['foo', -1, 'foo'],
            ['foo', -35, 'foo'],
            ['foo', 35, 'foo'],
            ['foo', 3, 'foo'],
            ['foo', 2, 'fo'],
            [' foo', 35, ' foo'],
            ['  foo', 3, '  f'],
            ['  foo', 2, '  '],
            ['  foo', 2, null, ['allowBlank' => false]],
            ['  f  o o ', 6, ' f o o', ['compactWhitespace' => true]],
            ['  foo', 2, 'fo', ['trimWhitespace' => true]],
            ['  f  o o  ', 4, 'f o', ['compactWhitespace' => true, 'trimWhitespace' => true]]
        ];
    }

    public function provideFailingToCoerceToBoolReturnsDefaultValueData()
    {
        $now = new DateTime();

        return [
            [154, [], null],
            [-34, [], null],
            ['10', [], null],
            [[1, 2, 3], [], null],
            [154, ['default' => true], true],
            [-34, ['default' => false], false],
            ['10', ['default' => 7736], 7736],
            [[1, 2, 3], ['default' => $now], $now],
            [[1, 2, 3], ['default' => ['a', 'b', 'c']], ['a', 'b', 'c']]
        ];
    }

    public function provideFailingToCoerceToDateTimeReturnsDefaultValueData()
    {
        return [
            ['foo', [], null],
            [(object)[], [], null],
            [
                [
                    'day' => 1,
                    'month' => 10,
                    'yearX' => 1978
                ],
                [],
                null
            ],
            [
                [
                    'day' => 1,
                    'monthX' => 10,
                    'year' => 1978
                ],
                [],
                null
            ],
            [
                [
                    'dayX' => 1,
                    'month' => 10,
                    'year' => 1978
                ],
                [],
                null
            ],
            [
                [
                    'day' => 1,
                    'month' => 10,
                ],
                [],
                null
            ],
            [
                [
                    'day' => 1,
                    'year' => 1978
                ],
                [],
                null
            ],
            [
                [
                    'month' => 10,
                    'year' => 1978
                ],
                [],
                null
            ],
            [
                [
                    'day' => 14,
                    'month' => 13,
                    'year' => 1978
                ],
                [],
                null
            ],
            ['foo', ['default' => true], true],
            [(object)[], ['default' => 'Hello, World!'], 'Hello, World!'],
            [
                [
                    'day' => 1,
                    'month' => 10
                ],
                ['default' => 1234],
                1234
            ],
            [
                [
                    'day' => 1
                ],
                [
                    'default' => [1, 2, 3]
                ],
                [1, 2, 3]
            ]
        ];
    }

    public function provideFailingToCoerceToEmailAddressReturnsDefaultValueData()
    {
        $now = new DateTime();

        return [
            ['  ', [], null],
            [(object)[], [], null],
            [true, ['default' => false], false],
            ['10', ['default' => 7736], 7736],
            [[1, 2, 3], ['default' => $now], $now],
            [[1, 2, 3], ['default' => ['a', 'b', 'c']], ['a', 'b', 'c']]
        ];
    }

    public function provideFailingToCoerceToFloatReturnsDefaultValueData()
    {
        $now = new DateTime();

        return [
            ['', [], null],
            ['foo', [], null],
            ['  ', [], null],
            [(object)[], [], null],
            [0, ['allowZero' => false], null],
            [0.0, ['allowZero' => false], null],
            ['0', ['allowZero' => false], null],
            ['0.0', ['allowZero' => false], null],
            ['  0.0', ['allowZero' => false], null],
            ['0.0  ', ['allowZero' => false], null],
            ['  0.0  ', ['allowZero' => false], null],
            [true, ['default' => false], false],
            [false, ['default' => true], true],
            ['x10', ['default' => 7736], 7736],
            [[1, 2, 3], ['default' => $now], $now],
            [[1, 2, 3], ['default' => ['a', 'b', 'c']], ['a', 'b', 'c']],
            [34.0, ['allowZero' => false, 'noGreaterThan' => 0], null],
            [-34.0, ['allowZero' => false, 'noLessThan' => 0], null]
        ];
    }

    public function provideFailingToCoerceToIntReturnsDefaultValueData()
    {
        $now = new DateTime();

        return [
            ['', [], null],
            ['foo', [], null],
            ['  ', [], null],
            [(object)[], [], null],
            [0, ['allowZero' => false], null],
            [0.0, ['allowZero' => false], null],
            ['0', ['allowZero' => false], null],
            ['0.0', ['allowZero' => false], null],
            ['  0.0', ['allowZero' => false], null],
            ['0.0  ', ['allowZero' => false], null],
            ['  0.0  ', ['allowZero' => false], null],
            [true, ['default' => false], false],
            [false, ['default' => true], true],
            ['x10', ['default' => 7736], 7736],
            [[1, 2, 3], ['default' => $now], $now],
            [[1, 2, 3], ['default' => ['a', 'b', 'c']], ['a', 'b', 'c']]
        ];
    }

    public function provideFailingToCoerceToStringReturnsDefaultValueData()
    {
        $now = new DateTime();

        return [
            [true, [], null],
            [false, [], null],
            [[], [], null],
            [(object)[], [], null],
            ['', ['allowBlank' => false], null],
            ['     ', ['allowBlank' => false], null],
            ["\n", ['allowBlank' => false], null],
            ["\r", ['allowBlank' => false], null],
            ["\t", ['allowBlank' => false], null],
            [" \n\r\t", ['allowBlank' => false], null],
            ['    ', ['allowBlank' => false, 'compactWhitespace' => true], null],
            [' a', ['allowBlank' => false, 'maxLength' => 1], null],
            [true, ['default' => false], false],
            [false, ['default' => true], true],
            [$now, ['default' => 7736], 7736],
            [[1, 2, 3], ['default' => $now], $now],
            [[1, 2, 3], ['default' => ['a', 'b', 'c']], ['a', 'b', 'c']]
        ];
    }

    /**
     * @param mixed $input
     * @param bool $expectedOutput
     * @dataProvider provideCoercesToBoolData
     */
    public function testCoercesToBool(
        $input,
        bool $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toBool($input)
        );
    }

    /**
     * @param mixed $input
     * @param DateTimeInterface $expectedOutput
     * @dataProvider provideCoercesToDateTimeData
     */
    public function testCoercesToDateTime(
        $input,
        DateTimeInterface $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toDateTime($input)
        );
    }

    /**
     * @param mixed $input
     * @param mixed $immutableOptionValue
     * @param DateTimeInterface $expectedOutput
     * @dataProvider provideCoercesToDateTimeUsingImmutableOptionData
     */
    public function testCoercesToDateTimeUsingImmutableOption(
        $input,
        $immutableOptionValue,
        DateTimeInterface $expectedOutput
    ) {
        $output = Coerce::toDateTime($input, ['immutable' => $immutableOptionValue]);

        self::assertEquals($expectedOutput, $output);
        self::assertInstanceOf(get_class($expectedOutput), $output);
    }

    /**
     * @param mixed $input
     * @param mixed $tzOptionValue
     * @param DateTimeInterface $expectedOutput
     * @dataProvider provideCoercesToDateTimeUsingTimezoneOptionData
     */
    public function testCoercesToDateTimeUsingTimezoneOption(
        $input,
        $tzOptionValue,
        DateTimeInterface $expectedOutput
    ) {
        /**
         * @var DateTimeInterface $output
         */
        $output = Coerce::toDateTime($input, ['tz' => $tzOptionValue]);

        self::assertEquals(
            $expectedOutput->format('c'),
            $output->format('c')
        );

        if ($input instanceof DateTimeInterface) {
            /*
             * If the input is an instance of DateTimeInterface, test to make
             * sure the output value is NOT the same instance as the input.
             */
            self::assertNotSame($expectedOutput, $input);
        }
    }

    /**
     * @param mixed $input
     * @param string $expectedOutput
     * @dataProvider provideCoercesToEmailAddressData
     */
    public function testCoercesToEmailAddress(
        $input,
        string $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toEmailAddress($input)
        );
    }

    /**
     * @param mixed $input
     * @param float $expectedOutput
     * @dataProvider provideCoercesToFloatData
     */
    public function testCoercesToFloat(
        $input,
        float $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toFloat($input)
        );
    }

    /**
     * @param mixed $input
     * @param $noGreaterThanOptionValue
     * @param float $expectedOutput
     * @dataProvider provideCoercesToFloatUsingNoGreaterThanOptionData
     */
    public function testCoercesToFloatUsingNoGreaterThanOption(
        $input,
        $noGreaterThanOptionValue,
        float $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toFloat($input, ['noGreaterThan' => $noGreaterThanOptionValue])
        );
    }

    /**
     * @param mixed $input
     * @param $noLessThanOptionValue
     * @param float $expectedOutput
     * @dataProvider provideCoercesToFloatUsingNoLessThanOptionData
     */
    public function testCoercesToFloatUsingNoLessThanOption(
        $input,
        $noLessThanOptionValue,
        float $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toFloat($input, ['noLessThan' => $noLessThanOptionValue])
        );
    }

    /**
     * @param mixed $input
     * @param int $expectedOutput
     * @dataProvider provideCoercesToIntData
     */
    public function testCoercesToInt(
        $input,
        int $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toInt($input)
        );
    }

    /**
     * @param mixed $input
     * @param $noGreaterThanOptionValue
     * @param float $expectedOutput
     * @dataProvider provideCoercesToIntUsingNoGreaterThanOptionData
     */
    public function testCoercesToIntUsingNoGreaterThanOption(
        $input,
        $noGreaterThanOptionValue,
        float $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toInt($input, ['noGreaterThan' => $noGreaterThanOptionValue])
        );
    }

    /**
     * @param mixed $input
     * @param $noLessThanOptionValue
     * @param float $expectedOutput
     * @dataProvider provideCoercesToIntUsingNoLessThanOptionData
     */
    public function testCoercesToIntUsingNoLessThanOption(
        $input,
        $noLessThanOptionValue,
        float $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toInt($input, ['noLessThan' => $noLessThanOptionValue])
        );
    }

    /**
     * @param mixed $input
     * @param string $expectedOutput
     * @dataProvider provideCoercesToPlainTextData
     */
    public function testCoercesToPlainText(
        $input,
        string $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toPlainText($input)
        );
    }

    /**
     * @param mixed $input
     * @param string $expectedOutput
     * @dataProvider provideCoercesToStringData
     */
    public function testCoercesToString(
        $input,
        string $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toString($input)
        );
    }

    /**
     * @param mixed $input
     * @param $compactWhitespaceOptionValue
     * @param string $expectedOutput
     * @param array $additionalOptions
     * @dataProvider provideCoercesToStringUsingCompactWhitespaceOptionData
     */
    public function testCoercesToStringUsingCompactWhitespaceOption(
        $input,
        $compactWhitespaceOptionValue,
        string $expectedOutput,
        array $additionalOptions = []
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toString(
                $input,
                array_merge(
                    $additionalOptions,
                    [
                        'compactWhitespace' => $compactWhitespaceOptionValue
                    ]
                )
            )
        );
    }

    /**
     * @param mixed $input
     * @param $maxLengthOptionValue
     * @param mixed $expectedOutput
     * @param array $additionalOptions
     * @dataProvider provideCoercesToStringUsingMaxLengthOptionData
     */
    public function testCoercesToStringUsingMaxLengthOption(
        $input,
        $maxLengthOptionValue,
        $expectedOutput,
        array $additionalOptions = []
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toString(
                $input,
                array_merge(
                    $additionalOptions,
                    [
                        'maxLength' => $maxLengthOptionValue
                    ]
                )
            )
        );
    }

    /**
     * @param mixed $input
     * @param array $options
     * @param mixed $expectedOutput
     * @dataProvider provideFailingToCoerceToBoolReturnsDefaultValueData
     */
    public function testFailingToCoerceToBoolReturnsDefaultValue(
        $input,
        array $options,
        $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toBool($input, $options)
        );
    }

    /**
     * @param mixed $input
     * @param array $options
     * @param mixed $expectedOutput
     * @dataProvider provideFailingToCoerceToDateTimeReturnsDefaultValueData
     */
    public function testFailingToCoerceToDateTimeReturnsDefaultValue(
        $input,
        array $options,
        $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toDateTime($input, $options)
        );
    }

    /**
     * @param mixed $input
     * @param array $options
     * @param mixed $expectedOutput
     * @dataProvider provideFailingToCoerceToEmailAddressReturnsDefaultValueData
     */
    public function testFailingToCoerceToEmailAddressReturnsDefaultValue(
        $input,
        array $options,
        $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toEmailAddress($input, $options)
        );
    }

    /**
     * @param mixed $input
     * @param array $options
     * @param mixed $expectedOutput
     * @dataProvider provideFailingToCoerceToFloatReturnsDefaultValueData
     */
    public function testFailingToCoerceToFloatReturnsDefaultValue(
        $input,
        array $options,
        $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toFloat($input, $options)
        );
    }

    /**
     * @param mixed $input
     * @param array $options
     * @param mixed $expectedOutput
     * @dataProvider provideFailingToCoerceToIntReturnsDefaultValueData
     */
    public function testFailingToCoerceToIntReturnsDefaultValue(
        $input,
        array $options,
        $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toInt($input, $options)
        );
    }

    /**
     * @param mixed $input
     * @param array $options
     * @param mixed $expectedOutput
     * @dataProvider provideFailingToCoerceToStringReturnsDefaultValueData
     */
    public function testFailingToCoerceToStringReturnsDefaultValue(
        $input,
        array $options,
        $expectedOutput
    ) {
        self::assertEquals(
            $expectedOutput,
            Coerce::toString($input, $options)
        );
    }
}
