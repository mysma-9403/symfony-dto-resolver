<?php
declare(strict_types=1);
namespace App\Common;

use App\Utils\ObjectHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class DTOResolver implements ValueResolverInterface
{
    public function __construct(private SerializerInterface $baseSerializer)
    {
        $this->baseSerializer = new Serializer(
            [new ArrayDenormalizer(), new PropertyNormalizer(null, null, new ReflectionExtractor())],
            [new JsonEncoder()]
        );
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($this->supports($argument)) {
            try {
                $serializer = $this->baseSerializer;
                $item = $serializer->deserialize($request->getContent(), $argument->getType(), 'json');
                try {
                    $item = ObjectHelper::trimInObject($item);
                } catch (\Throwable $e) {}
                yield $item;
            } catch (ExceptionInterface $exception) {
                throw new BadRequestHttpException($exception->getMessage(), $exception);
            }
        }
    }

    private function supports(ArgumentMetadata $argument): bool|int
    {
        return str_contains($argument->getType(), 'DTO');
    }
}
