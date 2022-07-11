<?php

declare(strict_types=1);

namespace Engelsystem\Test\Unit\Helpers;

use Engelsystem\Helpers\BarChart;
use Engelsystem\Renderer\Renderer;
use Engelsystem\Test\Unit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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

    /** @var Renderer&MockObject */
    private $rendererMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rendererMock = $this->mockRenderer(false);
        $this->mockTranslator();
    }

    public function testRender(): void
    {
        $this->rendererMock->expects(self::once())
            ->method('render')
            ->with('components/barchart', [
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
                'yLabels' => [
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
                ],
            ])
            ->willReturn('test bar chart');
        self::assertSame(
            'test bar chart',
            BarChart::render(self::ROW_LABELS, self::COLORS, self::DATA)
        );
    }

    public function provideBarChartClassTestData(): array
    {
        return [
            [10, 'barchart-20'], // number to be passed to BarChart::generateChartDemoData, expected class
            [20, 'barchart-40'],
            [25, 'barchart-50'],
        ];
    }

    /**
     * @dataProvider provideBarChartClassTestData
     */
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
}
