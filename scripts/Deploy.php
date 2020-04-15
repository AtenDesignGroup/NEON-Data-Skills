<?php

if (PHP_SAPI !== 'cli') {
    return;
}

/**
 * @var array
 */
const EXCLUDED_MARKDOWN_FILES = [
    "readme"
];

/**
 * @var string
 */
const HTTP_ENDPOINT = "https://master-7rqtwti-di4alr4iwbwyg.us-2.platformsh.site/neon-tutorials/webhook-capture";

// ******************************
// ******** CLI Script **********
// ******************************

$commitHash = $argv[1] ?? null;

foreach (findTutorialMarkdownFiles($commitHash) as $markdownFile) {
    if (!file_exists($markdownFile)) {
        continue;
    }
    $fileObject = new \SplFileObject($markdownFile);

    $fileObjectName = strtolower(
        $fileObject->getBasename('.md')
    );

    if (in_array($fileObjectName, EXCLUDED_MARKDOWN_FILES)) {
        continue;
    }

    if ($payload = buildContentPayload($fileObject)) {
        $response = httpPostPayload(HTTP_ENDPOINT, $payload);

        if ($response['code'] !== 200) {
            printf(
                "Error: Got a %d an when posting the payload for %s!",
                $response['code'],
                $fileObject->getRealPath()
            );
        }
    }
}

exit(0);

// ******************************
// ******* CLI Functions ********
// ******************************

/**
 * Find all the tutorial markdown files.
 *
 * @param string|null $commit
 *   The last commit hash that was deployed.
 *
 * @return array
 *   An array of all the markdown files.
 */
function findTutorialMarkdownFiles(string $commit = null): array
{
    $command = $commit
        ? "git diff --name-only HEAD {$commit} \"tutorials/*.md\""
        : 'git ls-files "tutorials/*.md"';

    $shellOutput = shell_exec($command);

    if (!isset($shellOutput)) {
        return [];
    }

    return array_filter(explode("\n", $shellOutput));
}

/**
 * HTTP post JSON payload.
 *
 * @param string $url
 *   The HTTP URL.
 * @param array $payload
 *   The payload data array.
 *
 * @return array
 *   An array of the response data.
 */
function httpPostPayload(string $url, array $payload): array
{
    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    return [
        'body' => $response,
        'code' => $responseCode,
    ];
}

/**
 * Build the Markdown content payload.
 *
 * @param \SplFileObject $fileObject
 * @return array
 *   An array of the payload structure.
 */
function buildContentPayload(\SplFileObject $fileObject): array
{
    $payload = [
        'metadata' => [],
        'content' => null,
    ];
    $fileObject->rewind();

    while ($fileObject->valid()) {
        $buffer = $fileObject->fgets();

        if (strpos(trim($buffer), '---') !== false) {
            while ($innerBuffer = trim($fileObject->fgets())) {
                if (strpos($innerBuffer, '---') !== false) {
                    continue 2;
                }
                $payload['metadata'][] = array_map(
                    'trim',
                    explode(':', $innerBuffer)
                );
            };
        }
        $payload['content'] .= $buffer;
    }

    return $payload;
}
