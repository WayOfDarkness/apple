<?php
  use Slim\Container as ContainerInterface;
  use \Psr\Http\Message\ServerRequestInterface as Request;
  use \Psr\Http\Message\ResponseInterface as Response;

  class Contact extends Illuminate\Database\Eloquent\Model {
    public $timestamps = false;
    protected $table = 'contact';

    public function store($data) {
      $contact = new Contact;
      $contact->name = $data['name'];
      $contact->phone = $data['phone'] ? $data['phone'] : '';
      $contact->email = $data['email'] ? $data['email'] : '';
      $contact->content = $data['content'];
      $contact->read = 0;
      $contact->reply = 0;
      $contact->status = 'active';
      $contact->created_at = date('Y-m-d H:i:s');
      $contact->updated_at = date('Y-m-d H:i:s');
      $contact->save();
      return $contact->id;
    }

    public function update($id, $type_status, $status) {
      $contact = Contact::where('status','!=','delete')->where('id', $id)->first();
      if (!$contact) return -2;
      switch ($type_status) {
        case 'read':
          $contact->read = ($status == 'read') ? 1 : 0;
          break;
        case 'reply':
          if ($status == 'reply') {
            $contact->reply = 1;
            $contact->read = 1;
          } else $contact->reply = 0;
          break;
        case 'display':
          if ($status == 'delete') $contact->status = 'delete';
          else if ($status == 'inactive') $contact->status = 'inactive';
          else $contact->status = 'active';
          break;
      }

      $contact->updated_at = date('Y-m-d H:i:s');
      $contact->save();
      return 0;
    }

    public function contactNew(){
      $read = Contact::where('status', 'active')->where('read', 0)->take(10)->get();
      $reply = Contact::where('status', 'active')->where('reply', 0)->take(10)->get();
      $data['read'] = $read;
      $data['reply'] = $reply;
      return $data;
    }
  }