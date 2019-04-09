<template>
  <section class="content">
    <vue-good-table
      mode="remote"
      :rows="items"
      :total-rows="totalRecords || items.length"
      :columns="columns"
      :search-options="tableOptions.search"
      :pagination-options="tableOptions.pagination"
      style-class="vgt-table striped"
      @on-page-change="onPageChange"
      @on-sort-change="onSortChange"
      @on-search="onSearch"
      @on-per-page-change="onPerPageChange"
    >
      <template #table-row="props">
        <span v-if="props.column.field === 'action'">
          {{ actions[props.row.action] }}（{{ props.row.action }}）
        </span>
        <span v-else-if="props.column.field === 'email'">
          {{ props.row.email }}&nbsp;
          <a :href="`${baseUrl}/admin/users?uid=${props.row.user_id}`" class="label label-primary">
            UID: {{ props.row.user_id }}
          </a>
        </span>
        <span v-else-if="props.column.field === 'player_name'">
          <span v-if="props.row.player_name">
            <span class="label label-green">
              PID: {{ props.row.player_id }}
            </span>
          </span>
          <span v-else>N/A</span>
        </span>
        <span v-else-if="props.column.field === 'parameters'">
          <a href="#" @click="showExtraParams(props.row.parameters)">点击查看</a>
        </span>
        <span v-else>
          {{ props.formattedRow[props.column.field] }}
        </span>
      </template>
    </vue-good-table>
  </section>
</template>

<script>
import { VueGoodTable } from 'vue-good-table'
import 'vue-good-table/dist/vue-good-table.min.css'

export default {
  components: {
    VueGoodTable,
  },
  props: {
    baseUrl: {
      type: String,
      default: blessing.base_url,
    },
  },
  data() {
    return {
      items: [],
      columns: [
        { field: 'id', label: '#', type: 'number' },
        { field: 'action', label: '动作' },
        { field: 'email', label: '用户邮箱' },
        { field: 'player_name', label: '角色名' },
        { field: 'parameters', label: '附加参数', sortable: false, globalSearchDisabled: true },
        { field: 'ip', label: 'IP', sortable: false },
        { field: 'time', label: '时间' },
      ],
      tableOptions: {
        search: {
          enabled: true,
          placeholder: '搜索',
        },
        pagination: {
          enabled: true,
          nextLabel: '下一页',
          prevLabel: '上一页',
          rowsPerPageLabel: '每页项目数',
          allLabel: '全部',
          ofLabel: '/',
        },
      },
      totalRecords: 0,
      serverParams: {
        sortField: 'id',
        sortType: 'asc',
        page: 1,
        perPage: 10,
        search: '',
      },
      actions: {
        authenticate: '登录',
        refresh: '刷新令牌',
        validate: '验证令牌',
        signout: '登出',
        invalidate: '吊销令牌',
        join: '请求加入服务器',
        has_joined: '进入服务器'
      },
    }
  },
  methods: {
    async fetchData() {
      const { data, totalRecords } = await blessing.fetch.get(
        '/admin/yggdrasil-log/data',
        this.serverParams
      )
      this.totalRecords = totalRecords
      this.items = data
    },
    showExtraParams(params) {
      alert('附加参数：\n' + params)
    },
    onPageChange(params) {
      this.serverParams.page = params.currentPage
      this.fetchData()
    },
    onPerPageChange(params) {
      this.serverParams.perPage = params.currentPerPage
      this.fetchData()
    },
    onSortChange([params]) {
      this.serverParams.sortType = params.type
      this.serverParams.sortField = params.field
      this.fetchData()
    },
    onSearch(params) {
      this.serverParams.search = params.searchTerm
      this.fetchData()
    },
  },
}
</script>
