(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["chunk-dff2aa3a"],{"2bb5":function(t,s,a){"use strict";s.__esModule=!0,s.default={mounted:function(){},methods:{getMigratingConfig:function(){return{props:{},events:{}}}}}},"6b7c":function(t,s,a){"use strict";s.__esModule=!0;var e=a("4897");s.default={methods:{t:function(){for(var t=arguments.length,s=Array(t),a=0;a<t;a++)s[a]=arguments[a];return e.t.apply(this,s)}}}},"8ec2":function(t,s,a){"use strict";var e=a("e690"),c=a.n(e);c.a},a5c6:function(t,s,a){"use strict";a.r(s);var e=function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("div",{staticClass:"row"},[a("div",{staticClass:"col-lg-6 col-md-6 col-sm-12"},[a("div",{staticClass:"card"},[t._m(0),a("div",{staticClass:"card-body form-card"},[a("el-upload",{staticClass:"upload-demo",attrs:{drag:"",action:t.host+"/admin/api/import_product","on-success":t.handleSuccessProduct,accept:".xlsx, .xls, .csv"}},[a("i",{staticClass:"el-icon-upload"}),a("div",{staticClass:"el-upload__text"},[t._v("Drop file here or "),a("em",[t._v("click to upload")])]),a("div",{staticClass:"el-upload__tip",attrs:{slot:"tip"},slot:"tip"},[t._v("Chấp nhận files xlsx, xls, csv")]),a("div",{staticClass:"el-upload__tip",attrs:{slot:"tip"},slot:"tip"},[a("a",{attrs:{href:t.host+"/static/nhap_san_pham.xlsx",download:""}},[t._v("File mẫu")])])])],1)])]),a("div",{staticClass:"col-lg-6 col-md-6 col-sm-12"},[a("div",{staticClass:"card"},[t._m(1),a("div",{staticClass:"card-body form-card"},[a("el-upload",{staticClass:"upload-demo",attrs:{drag:"",action:t.host+"/admin/api/uploadProductImage","on-success":t.handleSuccessImage,accept:".zip"}},[a("i",{staticClass:"el-icon-upload"}),a("div",{staticClass:"el-upload__text"},[t._v("Drop file here or "),a("em",[t._v("click to upload")])]),a("div",{staticClass:"el-upload__tip",attrs:{slot:"tip"},slot:"tip"},[t._v("Chấp nhận file zip")])])],1)])])])},c=[function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("div",{staticClass:"card-header"},[a("h6",{staticClass:"title"},[t._v("Sản phẩm")])])},function(){var t=this,s=t.$createElement,a=t._self._c||s;return a("div",{staticClass:"card-header"},[a("h6",{staticClass:"title"},[t._v("Hình ảnh")])])}],i=(a("0fb7"),a("450d"),a("f529")),o=a.n(i),l=(a("f225"),a("89a9")),n=a.n(l),d=(a("1951"),a("eedf")),r=a.n(d),u=(a("cadf"),a("551c"),a("097d"),{components:{ElButton:r.a,ElUpload:n.a},computed:{},data:function(){return{host:window.BASE_URL}},mounted:function(){this.$store.dispatch("setPageTitle","Nhập sản phẩm")},methods:{handleSuccessProduct:function(t,s,a){t.success?(o()({message:"Nhập sản phẩm thành công",type:"success"}),this.$router.push({name:"AllProducts"})):o()({message:"Có lỗi xảy ra",type:"danger"})},handleSuccessImage:function(t,s,a){t.code?o()({message:t.message,type:"danger"}):o()({message:"Nhập hình ảnh thành công",type:"success"})}},destroyed:function(){this.$store.dispatch("setCurrentActions",[])}}),p=u,h=(a("8ec2"),a("2877")),m=Object(h["a"])(p,e,c,!1,null,null,null);m.options.__file="Import-Product.vue";s["default"]=m.exports},e690:function(t,s,a){}}]);
//# sourceMappingURL=chunk-dff2aa3a.469240a9.js.map