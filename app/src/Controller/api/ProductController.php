<?php

namespace App\Controller\api;

use App\Entity\CountHist;
use App\Entity\PriceHist;
use App\Entity\Products;
use App\Repository\CategoryRepository;
use App\Repository\ProductsRepository;
use App\Repository\UserRepository;
use App\Validation\ProductValidator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations\Parameter;
use OpenApi\Annotations\Response;
use OpenApi\Annotations\Schema;
use OpenApi\Annotations\MediaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Exception\ValidatorException;

/**
 * Class PostController
 * @package App\Controller
 * @Route("/api", name="product_api")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/products", name="posts", methods={"GET"})
     */
    /**
     * @OpenApi\Annotations\Get(
     ** path="/api/company/{company_id}",
     *   tags={"Company"},
     *security={
     *{
     *"passport": {}},
     *},
     *   summary="Company Detail",
     *   operationId="companydetails",
     *   @Parameter(
     *      name="company_id",
     *      in="path",
     *      required=true,
     *      @Schema(
     *           type="integer"
     *      )
     *   ),
     *   @Response(
     *      response=200,
     *       description="Success",
     *      @MediaType(
     *          mediaType="application/json",
     *      )
     *   ),
     *   @Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *   @Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/

    public function getProducts(ProductsRepository $productRepository): JsonResponse
    {
        $data = $productRepository->findAll();
        $products = array();
        foreach ($data as $product) {
            $products[] = [
                'name' => $product->getName(),
                'price' => $product->getCurrPrice()
            ];
        }
        return new JsonResponse($products);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $repository
     * @return JsonResponse
     * @throws \Exception
     * @Route("/newproduct", name="new_product", methods={"POST"})
     */
    public function addProduct(Request $request, EntityManagerInterface $entityManager, CategoryRepository $repository)
    {

        try {
            $request = $this->transformJsonBody($request);
            $errors = (new ProductValidator())->validate($request->request->all());
            if (!empty($errors)) {
                throw new ValidatorException('Введённые данные некорректны: ' . implode('; ', $errors));
            }

            $product = (new Products())
                ->setName($request->get('name'))
                ->setStatusCount($request->get('count'))
                ->setCurrPrice($request->get('price'))
                ->setCategory($repository->find($request->get('category')));
            $entityManager->persist($product);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Post added successfully",
            ];
            return new JsonResponse($data);

        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return new JsonResponse($data, 422);
        }
    }

    /**
     * @param ProductsRepository $repository
     * @param $id
     * @return JsonResponse
     * @Route("/posts/{id}", name="posts_get", methods={"GET"})
     */
    public function getPost(ProductsRepository $repository, $id)
    {
        $post = $repository->find($id);

        if (!$post) {
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return new JsonResponse($data, 404);

        }
        return new JsonResponse($post->getName());
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $repository
     * @param $id
     * @return JsonResponse
     * @Route("/update-product/{id}", name="update_product", methods={"PUT"})
     */
    public function updateProduct(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, ProductsRepository $repository, $id)
    {

        try {
            $product = $repository->find($id);

            if (!$product) {
                $data = [
                    'status' => 404,
                    'errors' => "Post not found",
                ];
                return new JsonResponse($data, 404);

            }


            if ($request->get('price') != $product->getCurrPrice()) {
                $priceHist = (new PriceHist())
                    ->setCurrentPrice($product->getCurrPrice())
                    ->setDate(new DateTime(date("Y-m-d", time())))
                    ->setdelta($request->get('price') - $product->getCurrPrice())
                    ->setProduct($product);
                $entityManager->persist($priceHist);
            }
            if ($request->get('count') != $product->getStatusCount()) {

                $countHist = (new CountHist())
                    ->setCount($product->getStatusCount())
                    ->setDate(new DateTime(date("Y-m-d", time())))
                    ->setdelta($request->get('count') - $product->getStatusCount())
                    ->setProduct($product);
                $entityManager->persist($countHist);
            }

            $request = $this->transformJsonBody($request);
            $request = $this->transformJsonBody($request);
            $errors = (new ProductValidator())->validate($request->request->all());
            if (!empty($errors)) {
                throw new ValidatorException('Введённые данные некорректны: ' . implode('; ', $errors));
            }
            $product
                ->setName($request->get('name'))
                ->setStatusCount($request->get('count'))
                ->setCurrPrice($request->get('price'))
                ->setCategory($categoryRepository->find($request->get('category')));
            $entityManager->flush();

            $data = [
                'status' => 200,
                'errors' => "Post updated successfully",
            ];
            return new JsonResponse($data);


        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return new JsonResponse($data, 422);
        }

    }

    /**
     * @return JsonResponse
     * @Route("/delete-product/{id}", name="product_delete", methods={"DELETE"})
     */
    public function deleteProduct(EntityManagerInterface $entityManager, ProductsRepository $repository, $id)
    {
        $product = $repository->find($id);

        if (!$product) {
            $data = [
                'status' => 404,
                'errors' => "Post not found",
            ];
            return new JsonResponse($data, 404);

        }

        $entityManager->remove($product);
        $entityManager->flush();
        $data = [
            'status' => 200,
            'errors' => "Post deleted successfully",
        ];
        return new JsonResponse($data);
    }

    protected function transformJsonBody(\Symfony\Component\HttpFoundation\Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }

}