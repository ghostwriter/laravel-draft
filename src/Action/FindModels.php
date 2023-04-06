<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Action;

use Generator;
use Ghostwriter\Draft\ClassMap;
use Ghostwriter\Draft\Draft;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;

final class FindModels
{
    /**
     * @var array<string,array<string,array<string,Stmt>>>
     */
    private array $models = [];

    /**
     * @throws FileNotFoundException
     *
     * @return Generator<string,array<Stmt>>
     *
     */
    public function __invoke(Draft $draft, Filesystem $filesystem, ClassMap $classMap): Generator
    {
        $traverser = new NodeTraverser();
        $nameResolver = new NameResolver();
        $traverser->addVisitor($nameResolver);

        $nodeFinder = new NodeFinder();
        foreach ($filesystem->files($draft->modelPath()) as $file) {
            $path = $file->getRealPath();
            if (false === $path) {
                throw new FileNotFoundException();
            }

            /** @return array<Stmt> $models */
            $models = $traverser->traverse($draft->parse($filesystem->get($path), $file->getFilename()));
            foreach ($models as $model) {
                /** @var array<Class_> $classes */
                $classes = $nodeFinder->findInstanceOf($model, Class_::class);
                foreach ($classes as $class) {
                    $className = NodeExtractor::getName($class);
                    $classMap->addClass($className, $path);

                    $classConst = $this->models[$path][$className]['const'] = array_reduce(
                        $nodeFinder->findInstanceOf($class, ClassConst::class),
                        static fn (array $carry, ClassConst $constant): array =>
                            array_merge($carry, NodeExtractor::getConsts($constant)),
                        []
                    );
                    $classMap->addClassConsts($classConst, $className, $path);

                    $classMethod = $this->models[$path][$className]['method'] = array_reduce(
                        $nodeFinder->findInstanceOf($class, ClassMethod::class),
                        static fn (array $carry, ClassMethod $method) =>
                            array_merge(
                                $carry,
                                [
                                    NodeExtractor::getName($method) => NodeExtractor::getParams($method),
                                ]
                            ),
                        []
                    );
                    $classMap->addClassMethods($classMethod, $className, $path);

                    $classProperty = $this->models[$path][$className]['property'] = array_reduce(
                        $nodeFinder->findInstanceOf($class, Property::class),
                        static fn (array $carry, Property $property): array =>
                            array_merge(
                                $carry,
                                array_reduce(
                                    $property->props,
                                    static fn (array $carry, PropertyProperty $property) =>
                                        array_merge(
                                            $carry,
                                            [
                                                NodeExtractor::getName($property) =>
                                                    NodeExtractor::getDefault($property),
                                            ]
                                        ),
                                    []
                                )
                            ),
                        []
                    );
                    $classMap->addClassProperties($classProperty, $className, $path);
                }
            }
        }

        //        yield from $this->models;
        yield $classMap;
    }

    /**
     * @return array<Node>
     */
    public function getModels(): array
    {
        return $this->models;
    }
}
