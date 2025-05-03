<?php

namespace HandlerCore\models\dao;

use HandlerCore\components\Handler;
use HandlerCore\Environment;
use HandlerCore\models\FiltersCheckMode;
use HandlerCore\models\PaginationMode;

/**
 * Class QueryParams
 * Represents query parameters, such as paging, ordering, and filtering, for use in queries.
 */
class QueryParams
{
    private int $page=0;
    private ?int $cant_by_page = null;

    private bool $enable_paging = false;
    private bool $enable_order = false;

    private ?array $order_fields = null;

    /**
     * @var string
     */
    private string $pagination_replace_tag = "LIMIT";
    /**
     * @var string
     */
    private string $order_replace_tag = 'ORDER';
    private string $having_union = "HAVING";

    private ?string $filter_string = null;
    private array $filter_columns = [];

    private bool $loadFromRequest = true;


    private PaginationMode $paginationMode = PaginationMode::SQL_CALC_FOUND_ROWS;

    private FiltersCheckMode $filtersCheckMode = FiltersCheckMode::CHECK_IN_QUERY;

    static function newInstanceFromArray(array $params): QueryParams
    {
        $result = new QueryParams();
        if (isset($params["HAVING_UNION"])) {
            $result->setHavingUnion($params["HAVING_UNION"]);
        }

        return $result;
    }

    public function getFilterString(): ?string
    {
        return $this->filter_string;
    }

    public function setFilterString(string $filter_string): void
    {
        $this->filter_string = $filter_string;
    }

    public function getFilterColumns(): array
    {
        return $this->filter_columns;
    }

    public function setFilterColumns(array $filter_columns): void
    {
        $this->filter_columns = $filter_columns;
    }


    public function getHavingUnion(): string
    {
        return $this->having_union;
    }

    public function setHavingUnion(string $having_union): void
    {
        $this->having_union = $having_union;
    }


    /**
     * @return string|null
     */
    public function getPaginationReplaceTag(): ?string
    {
        return $this->pagination_replace_tag;
    }

    /**
     * @param string $pagination_replace_tag
     */
    public function setPaginationReplaceTag(string $pagination_replace_tag): void
    {
        $this->pagination_replace_tag = $pagination_replace_tag;
    }

    /**
     * @return string|null
     */
    public function getOrderReplaceTag(): ?string
    {
        return $this->order_replace_tag;
    }

    /**
     * @param string $order_replace_tag
     */
    public function setOrderReplaceTag(string $order_replace_tag): void
    {
        $this->order_replace_tag = $order_replace_tag;
    }


    /**
     * @param $cant_by_page
     * @param int $page
     */
    public function setEnablePaging($cant_by_page, int $page = 0): void
    {
        $this->enable_paging = true;
        $this->cant_by_page = $cant_by_page;
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function getCantByPage(): ?int
    {
        return $this->cant_by_page;
    }

    /**
     * @return bool
     */
    public function isEnablePaging(): bool
    {
        return $this->enable_paging;
    }

    /**
     * @return bool
     */
    public function isEnableOrder(): bool
    {
        return $this->enable_order;
    }


    public function addOrderField($field, bool $asc = true): void
    {

        if ($field && $field != '') {
            if ($asc) {
                $order_type = "ASC";
            } else {
                $order_type = "DESC";
            }

            $this->order_fields[$field] = $order_type;
        }

    }

    public function removeOrder(): void
    {
        $this->order_fields = array();
    }

    /**
     * @return array
     */
    public function getOrderFields(): array
    {
        if (is_null($this->order_fields)) {
            $this->order_fields = array();
        }
        return $this->order_fields;
    }

    public function copyFrom(QueryParams $old): void
    {
        $this->page = $old->page;

        $this->cant_by_page = $old->cant_by_page;
        $this->enable_paging = $old->enable_paging;
        $this->enable_order = $old->enable_order;
        $this->order_fields = $old->order_fields;
    }

    public function getPaginationMode(): PaginationMode
    {
        return $this->paginationMode;
    }

    public function setPaginationMode(PaginationMode $paginationMode): void
    {
        $this->paginationMode = $paginationMode;
    }

    public function getFiltersCheckMode(): FiltersCheckMode
    {
        return $this->filtersCheckMode;
    }

    public function setFiltersCheckMode(FiltersCheckMode $filtersCheckMode): void
    {
        $this->filtersCheckMode = $filtersCheckMode;
    }


    public function setCantByPage(int $cant_by_page): void
    {
        $this->cant_by_page = $cant_by_page;
    }

    public function isLoadFromRequest(): bool
    {
        return $this->loadFromRequest;
    }

    public function setLoadFromRequest(bool $loadFromRequest): void
    {
        $this->loadFromRequest = $loadFromRequest;
    }

    /**
     * Builds and returns a QueryParams object based on the provided settings or HTTP request attributes.
     *
     * @param QueryParams|null $qSettings Optional initial query settings. If null, a new QueryParams instance will be created.
     * @return QueryParams Populated QueryParams object with filter, order, and pagination settings derived from the input or HTTP request attributes.
     */
    static public function buildRequestQueryParams(?QueryParams $qSettings = null): QueryParams{

        if(!$qSettings){
            $qSettings = new QueryParams();
        }

        if($qSettings->isLoadFromRequest()) {

            $filters = Handler::getRequestAttr("FILTER");
            $columns = Handler::getRequestAttr("FILTER_KEYS");

            if ($filters) {
                $qSettings->setFilterString($filters);
            }

            if ($columns) {
                $columns = explode(",", $columns);
                $qSettings->setFilterColumns($columns);
            }

            $order_field = Handler::getRequestAttr("FIELD");


            if ($order_field) {
                if (is_array($order_field)) {
                    foreach ($order_field as $field => $asc) {
                        $order_type_asc = (!$asc || $asc == "A");
                        $qSettings->addOrderField($field, $order_type_asc);
                    }
                } else {
                    $asc = Handler::getRequestAttr("ASC");
                    $order_type_asc = (!$asc || $asc == "A");
                    $qSettings->addOrderField($order_field, $order_type_asc);
                }

            }

            $page = Handler::getRequestAttr("PAGE");
            $page_size = Handler::getRequestAttr("PAGE_SIZE") ?? $qSettings->getCantByPage() ?? Environment::$APP_DEFAULT_LIMIT_PER_PAGE;

            if ($page !== null) {

                $qSettings->setEnablePaging($page_size, intval($page));

            }
        }

        return $qSettings;
    }

}
