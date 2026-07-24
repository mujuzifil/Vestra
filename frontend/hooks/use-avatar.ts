"use client";

import { useMutation, useQueryClient } from "@tanstack/react-query";
import { uploadAvatar, deleteAvatar } from "@/lib/api/auth";

const PROFILE_KEY = ["auth", "profile"];

export function useAvatar() {
  const queryClient = useQueryClient();

  const uploadMutation = useMutation({
    mutationFn: (file: File) => uploadAvatar(file),
    onSuccess: (updated) => {
      queryClient.setQueryData(PROFILE_KEY, updated);
    },
  });

  const deleteMutation = useMutation({
    mutationFn: deleteAvatar,
    onSuccess: (updated) => {
      queryClient.setQueryData(PROFILE_KEY, updated);
    },
  });

  return {
    upload: uploadMutation.mutateAsync,
    isUploading: uploadMutation.isPending,
    remove: deleteMutation.mutateAsync,
    isDeleting: deleteMutation.isPending,
  };
}
