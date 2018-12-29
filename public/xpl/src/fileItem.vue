<template>
  <div :class="fileClass" :data-path="file.path" @click="$emit('click')" :title="file.name">
    <div class="xpl-file-wrapper" :class="file.thumb" :style="style">
        <div class="xpl-icon" :class="file.className"></div>
    </div>
    <div class="text">
      <div class="xpl-filename">{{file.name}}</div>
      <div class="xpl-info">{{file.time}} ({{file.size}})</div>
    </div>
    <div class="delete-btn" @click="$emit('delete')">x</div>
  </div>
</template>
<script>
import utils from './utils';

export default {
  name: 'file',
  props: {
    file: {
      type: Object,
      default: () => ({
        path: '',
        thumb: '',
        className: '',
        name: '',
        time: '',
        size: '',
        selected: false
      })
    }
  },
  data() {
    return {
      name: '123'
    }
  },
  computed: {
    background () {
      return [
        require('./assets/images/folder-icon.png'),
        require('./assets/images/icons-big.png'),
        require('./assets/images/icons-small.png'),
        require('./assets/images/loading.svg')
      ]
    },
    fileClass() {
      return `xpl-file ${this.file.selected ? 'active' : '' }`;
    },
    style() {
      return utils.getImageStyle(this.file.path);
    }
  }
}
</script>
<style lang="scss" scope>
  @import './assets/styles/mime.scss';
  @import './assets/styles/file.scss';
</style>
