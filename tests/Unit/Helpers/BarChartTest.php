<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Carbon\CarbonImmutable;
use Engelsystem\Helpers\BarChart;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Test\Unit\TestCase;
use Generator;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(BarChart::class, 'render')]
#[CoversMethod(BarChart::class, 'calculateChartGroups')]
#[CoversMethod(BarChart::class, 'calculateYLabels')]
#[CoversMethod(BarChart::class, 'generateChartDemoData')]
#[CoversMethod(BarChart::class, 'calculateBarChartClass')]
#[AllowMockObjectsWithoutExpectations]
class BarChartTest extends TestCase
{
    private const ROW_LABELS = [
        'a' => 'a label',
        'b' => 'b label',
    ];

    private const COLORS = [
        'a' => '#000',
        'b' => '#fff',
    ];

    private const DATA = [
        '2022-07-11' => [
            'day' => '2022-07-11',
            'a' => 1,
            'b' => 2,
        ],
    ];

    private Renderer&MockObject $rendererMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rendererMock = $this->mockRenderer(false);
        $this->stubTranslator(fn(string $key, array $replace = []) => $key == 'general.date' ? 'Y-m-d' : $key);
    }

    public static function provideRenderTestData(): Generator
    {
        $yLabels = [
            [
                'label' => '0',
                'bottom' => '0%',
            ],
            [
                'label' => '1',
                'bottom' => '20%',
            ],
            [
                'label' => '2',
                'bottom' => '40%',
            ],
            [
                'label' => '3',
                'bottom' => '60%',
            ],
            [
                'label' => '4',
                'bottom' => '80%',
            ],
            [
                'label' => '5',
                'bottom' => '100%',
            ],
        ];

        yield 'empty data' => [
            [],
            [
                'groups' => [],
                'colors' => self::COLORS,
                'rowLabels' => self::ROW_LABELS,
                'barChartClass' => '',
                'yLabels' => $yLabels,
            ],
        ];
        yield 'non-empty data' => [
            self::DATA,
            [
                'groups' => [
                    [
                        'bars' => [
                            [
                                'value' => 1,
                                'title' => "2022-07-11\na label: 1",
                                'height' => '20%',
                                'bg' => '#000',
                            ],
                            [
                                'value' => 2,
                                'title' => "2022-07-11\nb label: 2",
                                'height' => '40%',
                                'bg' => '#fff',
                            ],
                        ],
                        'label' => '2022-07-11',
                    ],
                ],
                'colors' => self::COLORS,
                'rowLabels' => self::ROW_LABELS,
                'barChartClass' => '',
                'yLabels' => $yLabels,
            ],
        ];
    }

    #[DataProvider('provideRenderTestData')]
    public function testRender(array $testData, array $expected): void
    {
        $this->rendererMock->expects(self::once())
            ->method('render')
            ->with('components/barchart', $expected)
            ->willReturn('test bar chart');
        self::assertSame(
            'test bar chart',
            BarChart::render(self::ROW_LABELS, self::COLORS, $testData)
        );
    }

    public static function provideBarChartClassTestData(): array
    {
        return [
            [5, ''],
            [10, 'barchart-20'], // number to be passed to BarChart::generateChartDemoData, expected class
            [20, 'barchart-40'],
            [25, 'barchart-50'],
        ];
    }

    #[DataProvider('provideBarChartClassTestData')]
    public function testRenderBarChartClass(int $testDataCount, string $expectedClass): void
    {
        $this->rendererMock->expects(self::once())
            ->method('render')
            ->willReturnCallback(
                function (string $template, array $data = []) use ($expectedClass) {
                    self::assertSame($expectedClass, $data['barChartClass']);
                    return '';
                }
            );
        BarChart::render(...BarChart::generateChartDemoData($testDataCount));
    }

    public function testGenerateChartDemoData(): void
    {
        $first = CarbonImmutable::now()->subDays(2)->format('Y-m-d');
        $second = CarbonImmutable::now()->subDays()->format('Y-m-d');
        $expected = [
            [
                'count' => 'arrived',
                'sum'   => 'arrived sum',
            ],
            [
                'count' => '#090',
                'sum'   => '#888',
            ],
            [
                $first  => [
                    'day'   => $first,
                    'count' => 5001.0,
                    'sum'   => 5001.0,
                ],
                $second => [
                    'day'   => $second,
                    'count' => 5001.0,
                    'sum'   => 10002.0,
                ],
            ],
        ];
        $data = BarChart::generateChartDemoData(2);
        $this->assertEquals($expected, $data);
    }
}
