<?php 
  namespace App\Http\Services\V1;
  use App\Models\Notification;

  class NotificationService
  {
      public function getAllNotifications()
      {
        return Notification::all();
      }

      public function getNotificationById(int $id)
      {
        return Notification::find($id);
      }

      public function createNotification(array $data)
      {
          Notification::create($data);
      }

      public function updateNotification(int $id, array $data)
      {
          Notification::where('id', $id)->update($data);
      }

      public function deleteNotification(int $id)
      {
          Notification::destroy($id);
      }
  }
?>