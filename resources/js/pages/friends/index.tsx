import { Head, Link } from '@inertiajs/react';
import { Users, UserPlus, Search } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { AppLayout } from '@/components/app-shell';

interface Friend {
    id: number;
    status: string;
    created_at: string;
    friend: {
        id: number;
        name: string;
        profile_photo_url: string | null;
        profile?: {
            city: string | null;
            country: string | null;
        };
    };
}

interface Pagination {
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    friends: Friend[];
    pagination: Pagination;
}

export default function FriendsIndex({ friends }: Props) {
    return (
        <AppLayout>
            <Head title="Friends" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold">Friends</h1>
                        <p className="text-muted-foreground mt-1">
                            People you connect with
                        </p>
                    </div>
                    <Link href="/friends/requests">
                        <Button variant="outline">
                            <UserPlus className="mr-2 h-4 w-4" />
                            Requests
                        </Button>
                    </Link>
                </div>

                {/* Search / Add Friends */}
                <Card>
                    <CardContent className="py-4">
                        <div className="flex items-center gap-4">
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <input
                                    type="text"
                                    placeholder="Search for friends..."
                                    className="w-full pl-10 pr-4 py-2 rounded-md border"
                                />
                            </div>
                            <Link href="/friends/requests">
                                <Button>Find Friends</Button>
                            </Link>
                        </div>
                    </CardContent>
                </Card>

                {/* Friends List */}
                {friends.length === 0 ? (
                    <Card>
                        <CardContent className="py-12 text-center">
                            <Users className="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                            <p className="text-muted-foreground">
                                No friends yet. Start connecting with people!
                            </p>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        {friends.map((friendship) => (
                            <Card key={friendship.id}>
                                <CardContent className="pt-6">
                                    <div className="flex items-center gap-4">
                                        {friendship.friend.profile_photo_url ? (
                                            <img
                                                src={friendship.friend.profile_photo_url}
                                                alt={friendship.friend.name}
                                                className="w-14 h-14 rounded-full"
                                            />
                                        ) : (
                                            <div className="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center">
                                                <span className="text-xl font-medium">
                                                    {friendship.friend.name.charAt(0)}
                                                </span>
                                            </div>
                                        )}
                                        <div className="flex-1 min-w-0">
                                            <Link href={`/profile/${friendship.friend.id}`} className="font-medium hover:underline truncate block">
                                                {friendship.friend.name}
                                            </Link>
                                            {friendship.friend.profile?.city && (
                                                <p className="text-sm text-muted-foreground">
                                                    {friendship.friend.profile.city}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                    <div className="mt-4 flex gap-2">
                                        <Link href={`/profile/${friendship.friend.id}`} className="flex-1">
                                            <Button variant="outline" className="w-full" size="sm">
                                                View Profile
                                            </Button>
                                        </Link>
                                        <Link href={`/messages/${friendship.friend.id}`} className="flex-1">
                                            <Button className="w-full" size="sm">
                                                Message
                                            </Button>
                                        </Link>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}