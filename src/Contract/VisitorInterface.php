<?php

declare(strict_types=1);

namespace Ghostwriter\Draft\Contract;

use PhpParser\Node;

interface VisitorInterface
{
    /**
     * Called once after traversal.
     *
     * Return value semantics: null:      $nodes stays as-is otherwise: $nodes is set to the return value
     *
     * @param Node[] $nodes Array of nodes
     *
     * @return null|Node[] Array of nodes
     */
    public function afterTraverse(array $nodes): ?array;

    /**
     * Called once before traversal.
     *
     * Return value semantics:
     *  - null:      $nodes stays as-is
     *  - otherwise: $nodes is set to the return value
     *
     * @param array<array-key,Node> $nodes Array of nodes
     *
     * @return null|array<array-key,Node> Array of nodes
     */
    public function beforeTraverse(array $nodes): ?array;

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CHILDREN, child nodes of the current node will not be traversed
     * for any visitors.
     *
     * For subsequent visitors NodeVisitor::enterNode() will still be called on the current node and
     * NodeVisitor::leaveNode() will also be invoked for the current node.
     */
    public static function dontTraverseChildren(): int;

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CURRENT_AND_CHILDREN, child nodes of the current node will not
     * be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will not be called as well. leaveNode() will be invoked for visitors that has
     * enterNode() method invoked.
     */
    public static function dontTraverseCurrentAndChildren(): int;

    /**
     * Called when entering a node.
     *
     * Return value semantics:
     *  - null => $node stays as-is
     *  - Visitor::dontTraverseChildren() => Children of $node are not traversed. $node stays as-is
     *  - Visitor::stopTraversal() => Traversal is aborted. $node stays as-is
     *  _ otherwise => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|int|Node Replacement node (or special return value)
     */
    public function enterNode(Node $node): null|int|Node;

    /**
     * Called when leaving a node.
     *
     * Return value semantics:
     *  - null => $node stays as-is
     *  - VisitorInterface::removeNode() => $node is removed from the parent array
     *  - VisitorInterface::stopTraversal() => Traversal is aborted. $node stays as-is
     *  - array (of Nodes) => The return value is merged into the parent array (at the position of the $node)
     *  - otherwise => $node is set to the return value
     *
     * @param Node $node Node
     *
     * @return null|array<array-key,Node>|int|Node Replacement node (or special return value)
     */
    public function leaveNode(Node $node): null|int|Node|array;

    /**
     * Remove a node that occurs in an array of nodes If returned by ::leaveNode().
     *
     * For subsequent visitors ::leaveNode() will still be invoked for the removed node.
     */
    public static function removeNode(): int;

    /**
     * Traversal is aborted If returned by ::enterNode() or ::leaveNode(). The ::afterTraverse() method will still be
     * invoked.
     */
    public static function stopTraversal(): int;
}
