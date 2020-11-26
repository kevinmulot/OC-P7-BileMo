<?php


namespace App\Service;


use App\Repository\ClientRepository;
use App\Repository\PhoneRepository;
use App\Repository\UserRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

class CacheManager
{
    private $phoneRepo;
    private $userRepo;
    private $clientRepo;

    public function __construct(ClientRepository $clientRepository, UserRepository $userRepository, PhoneRepository $phoneRepository)
    {
        $this->phoneRepo = $clientRepository;
        $this->userRepo = $userRepository;
        $this->phoneRepo = $phoneRepository;
    }

    /**
     * @param CacheInterface $cache
     * @param null $id
     * @throws InvalidArgumentException
     */
    public function deleteClientCache(CacheInterface $cache, $id = null): void
    {
        if ($id) {
            $cache->delete('client' . $id);
        }

        $clientCount = count($this->clientRepo->findAll());

        $itemsPerPage = 10;
        $pageCount = (int)ceil($clientCount / $itemsPerPage);

        for ($i = 1; $i < $pageCount; $i++) {
            $cache->delete('clients_list' . $i);
        }
    }

    /**
     * @param CacheInterface $cache
     * @param null $id
     * @throws InvalidArgumentException
     */
    public function deleteUserCache(CacheInterface $cache, $id = null): void
    {
        if ($id) {
            $cache->delete('user' . $id);
        }

        $userCount = count($this->userRepo->findAll());

        $itemsPerPage = 10;
        $pageCount = (int)ceil($userCount / $itemsPerPage);

        for ($i = 1; $i < $pageCount; $i++) {
            $cache->delete('users_list' . $i);
        }
    }

    /**
     * @param CacheInterface $cache
     * @param null $clientId
     * @throws InvalidArgumentException
     */
    public function deleteCustomerCache(CacheInterface $cache, $clientId = null): void
    {

        $userCount = count($this->userRepo->findAll());

        $itemsPerPage = 10;
        $pageCount = (int)ceil($userCount / $itemsPerPage);

        for ($i = 1; $i < $pageCount; $i++) {
            $cache->delete($clientId . 'users_list' . $i);
        }
    }

    /**
     * @param CacheInterface $cache
     * @param null $id
     * @throws InvalidArgumentException
     */
    public function deletePhoneCache(CacheInterface $cache, $id = null): void
    {
        if ($id) {
            $cache->delete('phone' . $id);
        }

        $phoneCount = count($this->phoneRepo->findAll());

        $itemsPerPage = 10;
        $pageCount = (int)ceil($phoneCount / $itemsPerPage);

        for ($i = 1; $i < $pageCount; $i++) {
            $cache->delete('phones_list' . $i);
        }
    }
}
