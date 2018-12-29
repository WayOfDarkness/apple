import Vue from 'vue'
import BootstrapVue from "bootstrap-vue"
import App from './App.vue'
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap-vue/dist/bootstrap-vue.css"
import { library } from '@fortawesome/fontawesome-svg-core'
import { faTimes, faWindowRestore, faAngleRight, faAngleDown, faEdit, faTrash } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

Vue.use(BootstrapVue);
library.add(faTimes);
library.add(faWindowRestore);
library.add(faAngleRight);
library.add(faAngleDown);
library.add(faEdit);
library.add(faTrash);
Vue.component('font-awesome-icon', FontAwesomeIcon)


new Vue({
  el: '#app',
  render: h => h(App)
})
