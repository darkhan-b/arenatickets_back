<template>
  <div>
    <div class="d-md-none d-block bg-themed">
      <div class="row">
        <div class="col">
          <i @click="collapsed = !collapsed"
             class="menu-collapser text-white d-md-none d-inline-block p-3 ti-menu"></i>
        </div>
      </div>
    </div>
    <div class="shadow" @click="collapsed = !collapsed"
         :class="{collapsed: collapsed }"></div>
    <div class="sidebar"
         :class="{collapsed: collapsed}"
         data-background-color="white"
         data-color="white"
         data-active-color="danger">
      <div class="sidebar-wrapper">
        <div class="border-bottom " @click="collapsed = !collapsed">
          <div class="logo text-center align-items-center d-flex start">
            <a class="font-weight-bold w-100 py-3 col text-center d-block" href="/admin#/show">
              <img class="admin-logo" src="/images/logo.png" alt="logo"/>
            </a>
            <span class="d-md-none d-inline-block position-absolute" style="right:20px;">
                            <i class="ti ti-close"></i>
                        </span>
          </div>
        </div>

        <ul class="pt-4 ps-4">
          <li class="d-block" v-for="(m,ind) in menu" v-if="!m.permission || permissions.includes(m.permission)">
            <template v-if="m.children">
              <a class="menu-item"
                 data-bs-toggle="collapse"
                 :href="'#collapse'+ind"
                 aria-expanded="false"
                 :aria-controls="'collapse'+ind">
                <i class="d-inline-block menu-icon"
                   :style="{ mcolor: m.color }"
                   :class="m.icon"></i>{{ m.title }} <span class="dropdown-arrow d-inline-block">
                                <i class="ti-angle-right"></i></span>
              </a>
              <ul v-if="m.children"
                  class="collapse"
                  :id="'collapse'+ind">
                <li v-for="ch in m.children"
                    class="d-block"
                    v-if="!ch.permission || permissions.includes(ch.permission)">
                  <a class="menu-item ps-4" v-if="ch.href" :href="ch.href" target="_blank">
                    {{ ch.title}}
                  </a>
                  <router-link v-else class="menu-item ps-4" :to="{name: ch.name, params: ch.params}">
                    {{ ch.title }}
                  </router-link>
                </li>
              </ul>
            </template>
            <template v-else>
              <router-link class="menu-item" :to="{name: m.name, params: m.params}">
                <i class="d-inline-block menu-icon"
                   :style="{ mcolor: m.color }"
                   :class="m.icon"></i>{{ m.title }}
              </router-link>
            </template>
          </li>
        </ul>

        <div class="position-fixed bg-white b-0 bottom-logout">
          <hr>
          <div class="ps-4">
            <div class="row align-items-center no-gutters">
              <div class="text-muted col-auto f-13">Admin, </div>
              <div class="col-auto">
                <form action="/logout" class="f-0" method="post">
                  <div v-html="window.csrf_field()"></div>
                  <input type="submit" id="logout-submit" class="text-underlined ps-0 py-0 btn-link btn ms-2" value="Выйти">
                </form>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script>
export default {
  mounted() {
    this.getMyPermissions()
  },
  methods: {
    makeActive(url) {
      this.activeUrl = url
    },
    getMyPermissions() {
      axios.get('/admin/mypermissions').then(res => {
        this.permissions = res.data
      })
    }
  },
  watch: {
    $route() {
      this.collapsed = true
    }
  },
  data: function() {
    return {
      collapsed: ($(window).width() < 768),
      permissions: [],
      menu: [
        // { name: 'dashboard', permission: "admin_panel", title: 'Главная', color: "#aaa", icon: 'ti-bar-chart'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'show' }, title: 'События', color: "#aaa", icon: 'ti-microphone'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'timetable' }, title: 'Сеансы', color: "#aaa", icon: 'ti-ticket'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'category' }, title: 'Категории', color: "#aaa", icon: 'ti-music-alt'},
        // { name: 'eloquent_list', permission: "admin_panel", params: { model: 'news' }, title: 'Новости', color: "#aaa", icon: 'ti-archive'},

        {
          name: 'eloquent_list', title: 'Площадки', color: "#aaa", icon: 'ti-home', children: [
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'city' }, title: 'Города'},
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'venue_category' }, title: 'Категории мест'},
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'venue' }, title: 'Площадки'},
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'venue_scheme' }, title: 'Варианты рассадки'},
          ]
        },

        {
          name: 'eloquent_list', title: 'Контент', color: "#aaa", icon: 'ti-archive', children: [
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'page' }, title: 'Страницы', color: "#aaa", icon: 'ti-book'},
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'slide' }, title: 'Слайды' },
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'banner' }, title: 'Баннера' },
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'carousel' }, title: 'Карусели' },
          ]
        },
        {
          permission: "admin_panel", title: 'Заказы', color: "#aaa", icon: 'ti-shopping-cart', children : [
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'order' }, title: 'Текущие' },
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'deleted_order' }, title: 'Удаленные' },
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'refund_application' }, title: 'Заявки на возврат' },
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'promocode' }, title: 'Промокоды' },
          ]
        },
        {
          permission: "admin_panel", title: 'Сторис', color: "#aaa", icon: 'ti-instagram', children : [
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'story_category' }, title: 'Категории' },
            { name: 'eloquent_list', permission: "admin_panel", params: { model: 'story_slide' }, title: 'Слайды' },
          ]
        },
        { name: 'report', permission: "admin_panel", title: 'Отчет', color: "#aaa", icon: 'ti-stats-up'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'meta' }, title: 'Мета', color: "#aaa", icon: 'ti-anchor'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'menu_item' }, title: 'Меню', color: "#aaa", icon: 'ti-menu'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'user' }, title: 'Пользователи', color: "#aaa", icon: 'ti-face-smile'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'subscription' }, title: 'Подписки', color: "#aaa", icon: 'ti-email'},
        // { name: 'eloquent_list', permission: "admin_panel", params: { model: 'activity_log' }, title: 'Логи', color: "#aaa", icon: 'ti-archive'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'setting' }, title: 'Настройки', color: "#aaa", icon: 'ti-settings'},
        { name: 'translation', permission: "admin_panel", params: {}, title: 'Переводы', color: "#aaa", icon: 'ti-ink-pen'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'api_partner' }, title: 'API партнеры', color: "#aaa", icon: 'ti-rocket'},
        { name: 'eloquent_list', permission: "admin_panel", params: { model: 'organizer_show' }, title: 'События на модерации', color: "#aaa", icon: 'ti-stamp'},
        {
          permission: "admin_panel", title: 'Документация', color: "#aaa", icon: 'ti-files', children : [
            { href: 'https://api.topbilet.kz/doc/widget', title: 'Виджет' },
            { href: 'https://api.topbilet.kz/doc/partner', title: 'Партнерам' },
            { href: 'https://api.topbilet.kz/scan/release.apk', title: 'Скан приложение' },
          ]
        },
      ],
      activeUrl: '',
    }
  }
}
</script>

<style scoped>
.dropdown-arrow {
  font-size: 11px;
  float: right;
  margin-right: 20px;
}

a[aria-expanded=true] .dropdown-arrow {
  -webkit-transform: rotate(90deg);
  -moz-transform: rotate(90deg);
  -ms-transform: rotate(90deg);
  -o-transform: rotate(90deg);
  transform: rotate(90deg);
}
.admin-logo {
  max-width: 130px;
  max-height: 60px;
}
</style>
