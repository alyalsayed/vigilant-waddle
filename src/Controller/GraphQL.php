<?php

namespace App\Controller;

use GraphQL\Utils\BuildSchema;
use GraphQL\Server\StandardServer;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Language\AST\TypeDefinitionNode;

class GraphQL
{
    private $users;
    private $posts;

    public function __construct()
    {
        // Sample data
        $this->users = [
            '1' => [
                'id' => '1',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'posts' => ['1', '2']
            ],
            '2' => [
                'id' => '2',
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'posts' => ['3']
            ]
        ];

        $this->posts = [
            '1' => [
                'id' => '1',
                'title' => 'First Post',
                'content' => 'This is the content of the first post.',
                'authorId' => '1'
            ],
            '2' => [
                'id' => '2',
                'title' => 'Second Post',
                'content' => 'This is the content of the second post.',
                'authorId' => '1'
            ],
            '3' => [
                'id' => '3',
                'title' => 'Third Post',
                'content' => 'This is the content of the third post.',
                'authorId' => '2'
            ]
        ];
    }

    public function handle()
    {
        try {
            // Load the schema from the file
            $schemaPath = __DIR__ . '/Schema/schema.graphql';
            if (!file_exists($schemaPath)) {
                throw new \Exception("Schema file not found at: $schemaPath");
            }

            // Type config decorator to add resolvers
            $typeConfigDecorator = function (array $typeConfig, TypeDefinitionNode $typeDefinitionNode): array {
                $name = $typeConfig['name'];

                // Add resolvers for User type
                if ($name === 'User') {
                    $typeConfig['resolveField'] = function ($user, $args, $context, ResolveInfo $info) {
                        $fieldName = $info->fieldName;

                        // Resolve User.posts
                        if ($fieldName === 'posts') {
                            return array_map(function ($postId) {
                                return $this->posts[$postId];
                            }, $user['posts']);
                        }

                        // Default resolver for other fields
                        return $user[$fieldName] ?? null;
                    };
                }

                // Add resolvers for Post type
                if ($name === 'Post') {
                    $typeConfig['resolveField'] = function ($post, $args, $context, ResolveInfo $info) {
                        $fieldName = $info->fieldName;

                        // Resolve Post.author
                        if ($fieldName === 'author') {
                            return $this->users[$post['authorId']];
                        }

                        // Default resolver for other fields
                        return $post[$fieldName] ?? null;
                    };
                }

                return $typeConfig;
            };

            // Build the schema with the type config decorator
            $schema = BuildSchema::build(file_get_contents($schemaPath), $typeConfigDecorator);

            // Define root resolvers
            $rootValue = [
                'users' => function () {
                    return array_values($this->users);
                },
                'user' => function ($root, $args) {
                    return $this->users[$args['id']] ?? null;
                },
                'posts' => function () {
                    return array_values($this->posts);
                },
                'post' => function ($root, $args) {
                    return $this->posts[$args['id']] ?? null;
                }
            ];

            // Create the GraphQL server
            $server = new StandardServer([
                'schema' => $schema,
                'rootValue' => $rootValue
            ]);

            // Handle the request
            $server->handleRequest();
        } catch (\Exception $e) {
            // Log the error and return a user-friendly message
            error_log($e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'errors' => [
                    [
                        'message' => 'An error occurred while processing your request.',
                        'locations' => [
                            [
                                'line' => 2,
                                'column' => 3
                            ]
                        ],
                        'path' => ['users']
                    ]
                ]
            ]);
        }
    }
}