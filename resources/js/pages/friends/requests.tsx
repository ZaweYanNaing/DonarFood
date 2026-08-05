import { Head } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { UserPlus, Check, X, Users } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { AppLayout } from '@/components/app-shell';

interface Friendship {
    id: number;
    status: string;
    created_at: string;
    user?: {
        id: number;
        name: string;
        profile_photo_url: string | null;
    };
    friend?: {
        id: number;
        name: string;
        profile_photo_url: string | null;
    };
}

interface Props {
    received: Friendship[];
    sent: Friendship[];
}

export default function FriendRequests({ received, sent }: Props) {
    const { post: acceptPost, processing: accepting } = useForm();

    return (
        <AppLayout>
            <Head title="Friend Requests" />

            <div className="space-y-6">
                <h1 className="text-3xl font-bold">Friend Requests</h1>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Received Requests */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <UserPlus className="h-5 w-5" />
                                Received ({received.length})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {received.length === 0 ? (
                                <p className="text-muted-foreground text-center py-8">
                                    No pending requests
                                </p>
                            ) : (
                                <div className="space-y-4">
                                    {received.map((request) => (
                                        <div key={request.id} className="flex items-center gap-4 p-3 border rounded-lg">
                                            {request.user?.profile_photo_url ? (
                                                <img
                                                    src={request.user.profile_photo_url}
                                                    alt={request.user.name}
                                                    className="w-12 h-12 rounded-full"
                                                />
                                            ) : (
                                                <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                                    <span className="text-lg font-medium">
                                                        {request.user?.name?.charAt(0)}
                                                    </span>
                                                </div>
                                            )}
                                            <div className="flex-1 min-w-0">
                                                <p className="font-medium truncate">{request.user?.name}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    {new Date(request.created_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                            <div className="flex gap-2">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => acceptPost(`/friends/${request.user?.id}`, {
                                                        data: { action: 'accept' }
                                                    })}
                                                    disabled={accepting}
                                                >
                                                    <Check className="h-4 w-4 text-green-600" />
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onClick={() => acceptPost(`/friends/${request.user?.id}`, {
                                                        data: { action: 'decline' }
                                                    })}
                                                    disabled={accepting}
                                                >
                                                    <X className="h-4 w-4 text-red-600" />
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Sent Requests */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Users className="h-5 w-5" />
                                Sent ({sent.length})
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            {sent.length === 0 ? (
                                <p className="text-muted-foreground text-center py-8">
                                    No pending sent requests
                                </p>
                            ) : (
                                <div className="space-y-4">
                                    {sent.map((request) => (
                                        <div key={request.id} className="flex items-center gap-4 p-3 border rounded-lg">
                                            {request.friend?.profile_photo_url ? (
                                                <img
                                                    src={request.friend.profile_photo_url}
                                                    alt={request.friend.name}
                                                    className="w-12 h-12 rounded-full"
                                                />
                                            ) : (
                                                <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                                    <span className="text-lg font-medium">
                                                        {request.friend?.name?.charAt(0)}
                                                    </span>
                                                </div>
                                            )}
                                            <div className="flex-1 min-w-0">
                                                <p className="font-medium truncate">{request.friend?.name}</p>
                                                <p className="text-sm text-muted-foreground">
                                                    Sent {new Date(request.created_at).toLocaleDateString()}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}