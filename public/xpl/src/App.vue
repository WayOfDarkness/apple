<template>
  <div id="explorer">
    <div class="header">
      <b-form-select v-model="classSize" :options="sizeOptions" class="mb-3 sl-size" text="Kích thước" />
      <!-- <b-dropdown id="ddown1" text="Tùy chọn" right variant="outline-success" class="m-md-2">
        <b-dropdown-item :disabled="!canSelectAll" @click="selectAll()">Chọn tất cả</b-dropdown-item>
        <b-dropdown-item :disabled="!canUnselect" @click="unselectAll()">Bỏ chọn tất cả</b-dropdown-item>
        <b-dropdown-item :disabled="!canUnselect" @click="requestConfirmDeleteSelectedFiles()">Xóa</b-dropdown-item>
        <b-dropdown-item :disabled="!canUnselect" @click="showMoveFileModal()">Chuyển qua thư mục</b-dropdown-item>
      </b-dropdown> -->
    </div>
    <div class="body">
        <b-col cols="3" id="folder-tree">
          <folder :path='"/"' :title='"ROOT"' />
        </b-col>
        <b-col v-if="isEmpty" cols="9" class="empty-folder">Thư mục trống</b-col>
        <b-col v-else cols="9" id="file-grid" :class="classSize" >
          <file v-for="file in files" :file="file" v-bind:key="file.name" @click="selectItem(file)" @delete="requestConfirmDeleteAFile(file)" />
        </b-col>
    </div>
    <div class="footer">
        <b-col cols="3" class="left">
          <b-button size="sm" variant="outline-success" @click="showCreateFolderModal()">+ Tạo thư mục</b-button>
        </b-col>
        <b-col cols="9" class="right">
          <b-button variant="outline-success" @click="showUploadFileModal()">Upload file</b-button>
          <div>
            <b-button variant="outline-warning" @click="close()">Đóng</b-button>
            <b-button variant="outline-primary" :class="checkSelect?'':'hidden'" @click="done()" >Xong</b-button>
          </div>
        </b-col>
    </div>
    <b-modal id="createFolderModal" ref="createFolderModal" title="Tạo thư mục">
      <b-form-input v-model="newFolderName" type="text" placeholder="Nhập tên thư mục" />
      <div slot="modal-footer" class="w-100 text-center">
        <b-btn variant="primary" @click="createFolder()">Tạo</b-btn>
      </div>
    </b-modal>
    <b-modal id="uploadFileModal" ref="uploadFileModal" size="lg" title="Upload file" @hidden="onUploadFileModalHidden">
      <vue-dropzone ref="myVueDropzone" id="dropzone" :options="dropzoneOptions"
        v-on:vdropzone-sending="sendingEvent"
        v-on:vdropzone-success="successEvent">
      </vue-dropzone>
      <div slot="modal-footer" class="w-100 text-center">
      </div>
    </b-modal>
    <b-modal id="deleteFileModal" ref="deleteFileModal" size="sm" title="Xóa file">
      Bạn có thật sự muốn xóa {{deleteFiles}} file?
      <div slot="modal-footer" class="w-100 space-around">
        <b-btn variant="danger" @click="deleteSelectedFiles()">Xóa</b-btn>
        <b-btn @click="closeDeleteFileModal()">Hủy</b-btn>
      </div>
    </b-modal>
    <b-modal id="deleteFolderModal" ref="deleteFolderModal" size="sm" title="Xóa file">
      Bạn có thật sự muốn xóa thư mục {{folderToDelete}}?
      <div slot="modal-footer" class="w-100 space-around">
        <b-btn variant="danger" @click="deleteFolder()">Xóa</b-btn>
        <b-btn @click="closeDeleteFolderModal()">Hủy</b-btn>
      </div>
    </b-modal>
    <b-modal id="renameFolderModal" ref="renameFolderModal" size="sm" title="Đổi tên folder">
      <b-form-input v-model="renameFolderName" type="text" placeholder="Nhập tên thư mục" />
      <div slot="modal-footer" class="w-100 text-center">
        <b-btn variant="primary" @click="renameFolder()">Đổi tên</b-btn>
      </div>
    </b-modal>
    <b-modal id="moveFileModal" ref="moveFileModal" size="sm" title="Di chuyển tập tin">
      <b-form-select v-model="destinationFolder" :options="selectFolderList" class="mb-3" />
      <div slot="modal-footer" class="w-100 text-center">
        <b-btn variant="primary" @click="moveFile()">Di chuyển tập tin</b-btn>
      </div>
    </b-modal>
    <vue-toast ref="toast"></vue-toast>
  </div>
</template>

<script>
import vueToast from 'vue-toast'
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'

import model from './model';
import utils from './utils';
import file from './fileItem.vue'
import folder from './folderItem.vue'

window.model = model;

export default {
  name: 'explorer',
  components: {
    file, folder,
    'vue-toast': vueToast,
    vueDropzone: vue2Dropzone,
  },
  data () {
    return {
      files: [],
      folders: [],
      folder: '',
      classSize: 'size-s',
      newFolderName: '',
      renameFolderName: '',
      sizeOptions: [
        { value: 'size-s', text: 'Nhỏ' },
        { value: 'size-m', text: 'Vừa' },
        { value: 'size-l', text: 'Lớn' },
      ],
      dropzoneOptions: {
        url: `${window.BASE_URL}`,
        thumbnailWidth: 150,
        maxFilesize: 8,
      },
      filesToDelete: [],
      folderToDelete: null,
      restFiles: [],
      destinationFolder: "",
      selectFolderList: [],
      iframId: "",
      mode: {}
    }
  },
  created() {
    var mode = utils.getParam("mode");
    mode = mode && JSON.parse(mode);
    this.mode = mode;
    console.log("MODE :: ", this.mode);

    this._data.iframeId = utils.getParam("iframeId");

    utils.bindEvent(window, 'message', function (msg) {
      var payload = {};
      try {
        payload = JSON.parse(msg.data);
      } catch (e) {}
      var data = payload.data || {};
      var iframeId = payload.iframeId || "";
      var event = payload.event || '__NOTHING__';

      if (iframeId && iframeId == (this._data.iframeId || utils.getParam("iframeId"))) {

        if (event == "MODE") {
          this.mode = data;
          console.log("MODE :: ", this.mode);
        }

        if (event == "CLEAR SELECTED FILE") {
          for (var file of this.files) {
            file.selected = false;
          }
        }
      }
    }.bind(this));

    utils.sendMessage('READY');
    this.$root.$on('FolderSelect', data => {
      this.files = data.files;
      this.folder = data.dir;
    });

    this.$root.$on('delete', data => {
      this.requestConfirmDeleteFolder(data.path);
    });

    this.$root.$on('rename', data => {
      this.showRenameFolderModal(data.path)
    });
  },
  mounted() {
    this.$refs.toast.setOptions({
      timeLife: 1000,
      position: 'bottom right',
    });
    window.toast = this.$refs.toast;
  },
  computed: {
    isEmpty() {
      return this.files.length === 0;
    },
    canSelectAll() {
      return this.files.find(f => !f.selected) && this.mode.multiple == true ? true : false;
    },
    canUnselect() {
      return this.files.find(f => f.selected) ? true : false;
    },
    deleteFiles() {
      return this.filesToDelete.length;
    },
    checkSelect() {
      const files = this.files.filter(f => f.selected);
      return files.length;
    }
  },
  methods:  {

    getFiles(dir) {
      this.folder = dir;
      model.getDir(dir).then(res => {
        this.files = res.files.map(f => utils.formatData(this.folder, f));
      });
    },
    selectAll() {
      this.files = this.files.map(f => ({...f, selected: true}));
    },
    unselectAll() {
      this.files = this.files.map(f => ({...f, selected: false}));
    },
    selectItem(file) {
      if (this.mode.multiple) {
        file.selected = !file.selected;
      } else {
        if (file.selected) {
          file.selected = false;
        } else {
          for(var f of this.files) {
            f.selected = false;
          }
          file.selected = true;
        }
      }
    },
    deleteSelectedFiles() {
      model.deleteFiles(this.filesToDelete).then(res => {
        if (!res.code) {
          this.files = this.restFiles;
        }
        this.closeDeleteFileModal();
      });
    },
    showCreateFolderModal() {
      this.$refs.createFolderModal.show();
    },
    closeCreateFolderModal() {
      this.$refs.createFolderModal.hide();
    },
    showUploadFileModal() {
      this.$refs.uploadFileModal.show();
    },
    showRenameFolderModal(f) {
      this.folder = f;
      this.$refs.renameFolderModal.show();
    },
    showMoveFileModal() {
      this.selectFolderList = this.folders.map(f => {
        return {
          value: f,
          text: f ? `/${f}` : "/"
        }
      });
      this.selectFolderList.push({
        value: "",
        text: "/"
      });

      this.destinationFolder = "";
      this.$refs.moveFileModal.show();
    },
    closeMoveFileModal() {
      this.$refs.moveFileModal.hide();
    },
    closeRenameFolderModal() {
      this.$refs.renameFolderModal.hide();
    },
    closeUploadFileModal() {
      this.$refs.uploadFileModal.hide();
    },
    onUploadFileModalHidden() {
      this.$refs.myVueDropzone.removeAllFiles();
    },
    sendingEvent(file, xhr, formData) {
      formData.append('folder', this.folder);
      formData.append('method', "uploadFile");
    },
    requestConfirmDeleteSelectedFiles() {
      this.filesToDelete = this.files.filter(f => f.selected).map(f => `${this.folder}/${f.name}`);
      this.restFiles = this.files.filter(f => !f.selected);
      this.$refs.deleteFileModal.show();
    },
    requestConfirmDeleteAFile(file) {
      this.filesToDelete = [`${this.folder}/${file.name}`];
      this.restFiles = this.files.filter(f => f.name !== file.name);
      this.$refs.deleteFileModal.show();
    },
    closeDeleteFileModal() {
      this.$refs.deleteFileModal.hide();
    },
    requestConfirmDeleteFolder(folder) {
      console.log('confirm delete folder', folder);
      this.folderToDelete = folder;
      this.$refs.deleteFolderModal.show();
    },
    deleteFolder() {
      let self = this;
      model.deleteFolder(this.folderToDelete).then(res => {
        if (!res.code) {
          this.$root.$emit('deleteFolder', {
            folderToDelete: this.folderToDelete
          })
        }
        this.closeDeleteFolderModal();
      });
    },
    closeDeleteFolderModal() {
      this.$refs.deleteFolderModal.hide();
    },
    successEvent(file, response) {
      const { code, message, data } = JSON.parse(response);
      if (!code) {
        const newFile = {};
        newFile.name =  data[0];
        newFile.time = file.lastModified;
        newFile.size = file.size;
        newFile.mime = file.type;
        this.files = [...this.files, utils.formatData(this.folder, newFile)];
        this.$root.$emit('uploadFilesSuccess');
      }
    },
    createFolder() {
      model.createFolder(this.folder + '/' + this.newFolderName).then(res => {
        if (!res.code) {
          this.$root.$emit('createFolder', {
            path: this.folder + '/' + this.newFolderName
          });
          this.newFolderName = '';
          this.closeCreateFolderModal();
        } else {
          console.log(message);
          this.$refs.toast.showToast('Thư mục đã tồn tại', {
            theme: 'error',
          });
        }
      });
    },
    moveFile() {
      var filesToMove = this.files.filter(f => f.selected).map(f => `${this.folder}/${f.name}`);
      var restFiles = this.files.filter(f => !f.selected);
      model.moveFile(filesToMove, this.destinationFolder).then(res => {
        if (!res.code) {
          this.destinationFolder = "";
          this.files = restFiles;
          this.closeMoveFileModal();
        }
        else {
          this.$refs.toast.showToast(res.message, {
            theme: 'error',
          });
        }
      })
    },
    renameFolder() {
      let parts = this.folder.split('/');
      parts.pop();
      parts.push(this.renameFolderName);
      let newName = parts.join('/');
      model.renameFolder(newName, this.folder).then(res => {
        if (!res.code) {
          let eventData = {
            oldName: this.folder,
            newName: newName,
            newShortName: this.renameFolderName
          };
          this.$root.$emit('renameFolder', eventData);
          this.renameFolderName = '';
          this.closeRenameFolderModal();
        }
        else {
          this.$refs.toast.showToast(res.message, {
            theme: 'error',
          });
        }
      });
    },
    close() {
      utils.sendMessage('CLOSE_MODAL');
    },
    done() {
      const files = this.files.filter(f => f.selected);
      utils.sendMessage('CHOOSE_FILE', { files });
    }
  }
}
</script>

<style lang="scss" scope>
  @import './assets/styles/explorer.scss';
  .hidden{
    display: none;
  }
</style>
