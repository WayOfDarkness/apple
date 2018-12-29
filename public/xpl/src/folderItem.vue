<template>
  <div class="xpl-folder-wrapper" :class="{active: isActive}">
    <div class="xpl-folder" :class="{active: selected}" @click="getFilesAndFolders(path)">
      <div class="left">
        <div class="left-icon">
          <div v-if="children > 0">
            <font-awesome-icon icon="angle-down" v-if="isActive" class="before-icon"/>
            <font-awesome-icon icon="angle-right" v-else class="before-icon"/>
          </div>
        </div>
        <img :src="selected?image.openFolderIcon:image.closedFolderIcon" alt="folder" class="folder-icon">
        <div class="xpl-folder-name">{{title}}</div>
      </div>
      <div class="right action-icon" v-if="path != '/'">
        <span class="color white" @click.stop="emitEvent('rename', path)">
          <font-awesome-icon icon="edit"/>
        </span>
        <span class="color red" @click.stop="emitEvent('delete', path)">
          <font-awesome-icon icon="trash"/>
        </span>
      </div>
    </div>
    <div class="subfolders">
      <folder
        v-for="c in folders"
        :key="c.path"
        :title='c.title'
        :path='c.path'
        :children='c.children'
      />
    </div>
  </div>
</template>

<style>
  .xpl-folder-wrapper:not(.active) .subfolders {
    display: none;
  }
  .folder-icon{
    margin: 5px;
    height: 80%
  }
  .red{
    color: #E23642 !important;
  }
  .left-icon{
    width: 10px;
  }
</style>


<script>
  import model from './model';
  import {setFolder, getFolder} from './model';
  import utils from './utils';
  import file from './fileItem.vue';

  const folder = {
    name: "folder",
    props: {
      path: {
        type: String,
        default: '/'
      },
      name: {
        type: String,
        default: ""
      },
      title: {
        type: String,
        default: 'ROOT'
      },
      firstActive: {
        type: Boolean,
        default: false
      },
      children: {
        type: Number,
        default: 1
      }
    },

    computed: {
      active() {
        return this.isActive || false;
      },
      open() {
        return this.isOpen
      },
      currentFolder() {
        return getFolder()
      }
    },
    data() {
      return {
        image: {
          folderIcon: require("./assets/images/folder-icon.png"),
          closedFolderIcon: require("./assets/images/closed-folder-icons.png"),
          openFolderIcon: require("./assets/images/open-folder-icons.png"),
          downIcon: require("./assets/images/down-icon.png"),
          rightIcon: require("./assets/images/right-icon.png")
        },
        files: [],
        folders: [],
        isFetched: false,
        isActive: false,
        isOpen: false,
        selected: false
      }
    },
    mounted() {
      this.isActive = this.firstActive;
      this.$root.$on('FolderSelect', data => {
        if (data.dir == this.path) {
          this.selected = true;
        } else {
          this.selected = false;
        }
      });

      // if (this.path == '/') {
      //   this.$root.$on('deleteFolder', data => {
      //     this.folders = this.folders.filter(x => x.path !== data.folderToDelete);
      //     this.getFilesAndFolders(this.path, true);
      //   });
      // }

      this.$root.$on('deleteFolder', data => {
        this.folders = this.folders.filter(x => x.path !== data.folderToDelete);
        let parent = getParentFolder(data.newName);
        if (this.path == parent) {
          this.getFilesAndFolders(this.path, true);
        }
      });

      this.$root.$on('renameFolder', data => {
        let parent = getParentFolder(data.newName);
        if (this.path == parent) {
          this.getFilesAndFolders(this.path, true);
        }
      });

      this.$root.$on('createFolder', data => {
        let parent = getParentFolder(data.path);
        if (this.path == parent) {
          console.log('.parent', parent);
          this.getFilesAndFolders(this.path, true);
        }
      });

      this.$root.$on('uploadFilesSuccess', () => {
        this.isFetched = false;
      });
    },
    methods: {
      getFilesAndFolders(path, force = false) {
        this.isActive = !this.isActive;
        this.isOpen = !this.isOpen;

        if (this.isFetched && !force) {
          this.$root.$emit('FolderSelect', {
            folders: this.folders,
            files: this.files,
            dir: this.path
          })
          setFolder(this.path);
          return;
        }
        if (force) {
          this.folders.splice(0, this.folders.length);
        }
        model.getDir(path).then(res => {
          this.files = res.files.map(f => utils.formatData(path, f));
          let _folders = [];
          let prefix = path;
          if (prefix == '/') {
            prefix = '';
          }
          for (let f of res.folders) {
            console.log(f);
            let fTitle = f.title;
            let children = f.children;
            let obj = {
              title: fTitle.length<30?fTitle:fTitle.substring(0,10) + '...',
              path: prefix + '/' + f.title,
              children: children
            };
            _folders.push(obj);
          }
          console.log(_folders);
          this.folders = _folders;
          this.isFetched = true;

          this.$root.$emit('FolderSelect', {
            folders: this.folders,
            files: this.files,
            dir: this.path
          })
          setFolder(this.path);
          if (force) {
            this.isActive = true;
          }
        });
      },
      emitEvent(event, path) {
        this.$root.$emit(event, {
          path: path
        })
      }
    }
  };

  function getParentFolder(path) {
    let parts = path.split('/');
    console.log('PARTS___', parts);
    parts.pop();
    if (parts.length == 1) {
      return '/';
    }
    return parts.join('/');
  }
  export default folder;
</script>
