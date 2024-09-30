<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\SystemException;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity;

class OnlyAvailableCarsComponent extends CBitrixComponent
{
    protected $userPosition;

    public function executeComponent()
    {
        try {
            $this->userPosition = $this->getCurrentUserPosition();
            $this->arResult = $this->getResult();
            $this->includeComponentTemplate();
        } catch (SystemException $e) {
            $this->arResult = [
                'error' => $e->getMessage(),
                'cars' => []
            ];
            $this->includeComponentTemplate('error');
        }
    }

    protected function getCurrentUserPosition()
    {
        global $USER;
        
        if (!$USER->IsAuthorized()) {
            throw new SystemException('Пользователь не авторизован');
        }
        
        $userId = $USER->GetID();
        $userInfo = \CUser::GetByID($userId)->Fetch();
        
        if (!$userInfo) {
            throw new SystemException('Не удалось получить информацию о пользователе');
        }
        
        $positionId = $userInfo['UF_USER_POSITION'];
        
        if (!$positionId) {
            throw new SystemException('У пользователя не указана должность');
        }
        
        return $positionId;
    }

    protected function getResult()
    {
        $startDate = $this->request->get('start_date');
        $endDate = $this->request->get('end_date');

        if (!$startDate || !$endDate) {
            throw new SystemException('Не указаны даты начала и окончания поездки');
        }

        $comfortCategories = $this->getComfortCategoriesForPosition($this->userPosition);
        $cars = $this->getAvailableCars($startDate, $endDate, $comfortCategories);

        return [
            'error' => null,
            'cars' => $cars
        ];
    }

    protected function getComfortCategoriesForPosition($positionId)
    {
        $hlblock = HighloadBlockTable::getById($this->arParams['POSITIONS_HLBLOCK_ID'])->fetch();
        $entity = HighloadBlockTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();
        
        $result = $entityDataClass::getList([
            'filter' => ['ID' => $positionId],
            'select' => ['UF_COMFORT_CATEGORIES']
        ])->fetch();
        
        if (!$result) {
            throw new SystemException('Не удалось получить категории комфорта для должности');
        }
        
        return $result['UF_COMFORT_CATEGORIES'];
    }

    protected function getAvailableCars($startDate, $endDate, $comfortCategories)
    {
        $filter = [
            'IBLOCK_ID' => $this->arParams['CARS_IBLOCK_ID'],
            'ACTIVE' => 'Y',
            'PROPERTY_COMFORT_CATEGORY' => $comfortCategories,
            '!ID' => $this->getBookedCarsIds($startDate, $endDate)
        ];

        $select = [
            'ID', 'NAME', 'PROPERTY_MODEL', 'PROPERTY_COMFORT_CATEGORY', 'PROPERTY_DRIVER'
        ];

        $result = \CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $filter,
            false,
            false,
            $select
        );

        $cars = [];
        while ($car = $result->GetNext()) {
            $cars[] = [
                'MODEL' => $car['PROPERTY_MODEL_VALUE'],
                'COMFORT_CATEGORY' => $car['PROPERTY_COMFORT_CATEGORY_VALUE'],
                'DRIVER' => $this->getDriverName($car['PROPERTY_DRIVER_VALUE'])
            ];
        }

        return $cars;
    }

    protected function getBookedCarsIds($startDate, $endDate)
    {
        $filter = [
            'IBLOCK_ID' => $this->arParams['BOOKINGS_IBLOCK_ID'],
            'ACTIVE' => 'Y',
            [
                'LOGIC' => 'OR',
                [
                    '<=PROPERTY_START_DATE' => $startDate,
                    '>=PROPERTY_END_DATE' => $startDate
                ],
                [
                    '<=PROPERTY_START_DATE' => $endDate,
                    '>=PROPERTY_END_DATE' => $endDate
                ],
                [
                    '>=PROPERTY_START_DATE' => $startDate,
                    '<=PROPERTY_END_DATE' => $endDate
                ]
            ]
        ];

        $select = ['PROPERTY_CAR'];

        $result = \CIBlockElement::GetList(
            [],
            $filter,
            false,
            false,
            $select
        );

        $bookedCarsIds = [];
        while ($booking = $result->GetNext()) {
            $bookedCarsIds[] = $booking['PROPERTY_CAR_VALUE'];
        }

        return $bookedCarsIds;
    }

    protected function getDriverName($driverId)
    {
        if (!$driverId) {
            return 'Не указан';
        }
        
        $driver = \CUser::GetByID($driverId)->Fetch();
        return $driver ? $driver['NAME'] . ' ' . $driver['LAST_NAME'] : 'Не указан';
    }
}