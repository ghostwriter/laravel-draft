<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Action;

use Generator;
use Ghostwriter\Draft\Draft;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use RuntimeException;

final class FindModels
{
    /**
     * @var array<Node>
     */
    private array $models = [];
    private NodeExtractor $nodeNameResolver;

    public function __construct()
    {
        $this->nodeNameResolver = new NodeExtractor();
    }

    /**
     * @throws FileNotFoundException
     *
     * @return Generator<string,array<Stmt>>
     *
     */
    public function __invoke(Draft $draft, Filesystem $filesystem): Generator
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
            foreach ($models as $model){
                /** @var array<Class_> $classes */
                $classes = $nodeFinder->findInstanceOf($model, Class_::class);
                foreach ($classes as $class){
                    $className = NodeExtractor::getName($class);

                    /** @var array<ClassConst> $constants */
                    $constants = $nodeFinder->findInstanceOf($class, ClassConst::class);
                    foreach ($constants as $constant){
                        $constName = NodeExtractor::getName($constant);
                        $constValue = NodeExtractor::getValue($constant);

                        $this->models[$path][$className]['const'][$constName] = $constValue;
                    }

                    /** @var array<ClassMethod> $methods */
                    $methods = $nodeFinder->findInstanceOf($class, ClassMethod::class);
                    foreach ($methods as $method){
                        $methodName = NodeExtractor::getName($method);
                        $methodParams = NodeExtractor::getParams($method);

                        $this->models[$path][$className]['methods'][$methodName] = $methodParams;
                    }

                    /** @var array<Property> $properties */
                    $properties = $nodeFinder->findInstanceOf($class, Property::class);
                    foreach ($properties as $property){
                        foreach ($property->props as $propertyProperty) {
                            $propertyName = NodeExtractor::getName($propertyProperty);
                            $propertyValue = NodeExtractor::getDefault($propertyProperty);

                            $this->models[$path][$className]['properties'][$propertyName] = $propertyValue;
                        }
                    }
                }
            }

        }

        yield from $this->models;
    }

    /**
     * @return array<Node>
     */
    public function getModels(): array
    {
        return $this->models;
    }
}
